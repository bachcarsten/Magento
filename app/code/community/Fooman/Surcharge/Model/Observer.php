<?php
/*
 * @author     Kristof Ringleff
 * @package    Fooman_Surcharge
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
*/

class Fooman_Surcharge_Model_Observer
{

    /**
     * add a dynamic rewrite for the paypal standard model
     * only required on Mage 1.4.1.1 and below
     */
    public function controllerFrontInitBefore()
    {
        if (version_compare(Mage::getVersion(), '1.4.1.1', '<')) {
            Mage::getConfig()->setNode(
                'global/models/paypal/rewrite/standard', 'Fooman_Surcharge_Model_Paypal_Standard'
            );
        }
    }

    /**
     * The subtotal block Mage_Tax_Block_Checkout_Subtotal only takes into consideration known
     * totals (subtotal and shipping) and their taxes when displaying a tax inclusive total
     * when using Fixed Product Taxes (FPT)
     * fix this for Surcharge Tax in this observer
     * triggered by event core_block_abstract_to_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function adjustSubtotal($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Tax_Block_Checkout_Subtotal
            && version_compare(Mage::getVersion(), '1.4.1.0', '<')
        ) {
            Mage::helper('surcharge/fixes')->adjustCheckoutSubtotal($block);
        }
    }

    /**
     * Reset the surcharge before quote is updated
     * triggered by event sales_quote_collect_totals_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function resetSurcharge($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if ($quote) {
            $quote->setFoomanSurchargeAmount(0);
            $quote->setBaseFoomanSurchargeAmount(0);
            $quote->setFoomanSurchargeTaxAmount(0);
            $quote->setBaseFoomanSurchargeTaxAmount(0);
            $quote->setFoomanSurchargeDescription();
            $quote->setFoomanSurchargeProcessed(false);

            /*
            //code to set default payment method checkmo
            if (Mage::getStoreConfig()) {
                if (!$quote->getPayment()->getMethod()) {
                        $quote->getPayment()->importData(array('method'=>'checkmo'));
                }
            }*/

        }
    }

    /**
     * currently unused
     * code to change the surcharge amount for a creditmemo
     *
     * observes event adminhtml_sales_order_creditmemo_register_before
     *
     * @param $observer
     */
    public function adjustCreditmemoSurcharge($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $request = $observer->getEvent()->getRequest();

        $surchargeRefund = $request->getParam('creditmemo');
        //surcharge_amount
        if (isset($surchargeRefund['surcharge_amount'])) {
            $surchargeAmount = $surchargeRefund['surcharge_amount'];
            $creditmemo->setBaseFoomanSurchargeAmount($surchargeAmount);
            $creditmemo->setFoomanSurchargeAmount(
                $creditmemo->getStore()->roundPrice(
                    $surchargeAmount * $creditmemo->getOrder()->getStoreToOrderRate()
                )
            );
        }
    }

    /**
     * add surcharge as a line item when sending quote info to paypal
     * observes event paypal_prepare_line_items
     *
     * @param $observer
     */
    public function addSurchargeToPaypalCart($observer)
    {
        Mage::helper('surcharge')->debug('OBSERVER $addSurchargeToPaypalCart');
        $paypalCart = $observer->getEvent()->getPaypalCart();
        $additional = $observer->getEvent()->getAdditional();
        $salesEntity = $observer->getEvent()->getSalesEntity();
        if ($additional instanceof Varien_Object && $salesEntity instanceof Mage_Core_Model_Abstract) {
            if ($salesEntity->getBaseFoomanSurchargeAmount() != 0) {
                $items = $additional->getItems();
                $items[] = new Varien_Object(
                    array(
                         'id'     => $this->_convertDescriptionToId($salesEntity->getFoomanSurchargeDescription()),
                         'name'   => $this->_getDescription($salesEntity),
                         'qty'    => 1,
                         'amount' => round($salesEntity->getBaseFoomanSurchargeAmount(), 2),
                    )
                );
                $salesEntity->setBaseSubtotal(
                    $salesEntity->getBaseSubtotal() + $salesEntity->getBaseFoomanSurchargeAmount()
                );
                $additional->setItems($items);
            }
        } elseif ($paypalCart) {
            if ($paypalCart->getSalesEntity()->getBaseFoomanSurchargeAmount() > 0) {
                Mage::helper('surcharge')->debug('OBSERVER $addSurchargeToPaypalCart $paypalCart->addItem');
                $paypalCart->addItem(
                    $this->_getDescription($paypalCart->getSalesEntity()),
                    1,
                    $paypalCart->getSalesEntity()->getBaseFoomanSurchargeAmount(),
                    $this->_convertDescriptionToId($paypalCart->getSalesEntity()->getFoomanSurchargeDescription())
                );
                if ($paypalCart->isShippingAsItem()) {
                    //if shipping is added as line item - the above addItem('surcharge') will make shipping count twice
                    $paypalCart->updateTotal(
                        Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL,
                        -1 * $paypalCart->getSalesEntity()->getBaseShippingAmount()
                    );
                }
            }
        }
    }

    /**
     * create an id based on the description that can be used for Paypal
     *
     * @param $description
     *
     * @return Fooman_Surcharge_Helper_Data|mixed
     */
    protected function _convertDescriptionToId($description)
    {
        $description = preg_replace(
            "/[^a-z0-9]+/", "", strtolower($description)
        );
        if (empty($description)) {
            return Mage::helper('surcharge')->__('Surcharge');
        } else {
            return $description;
        }
    }

    /**
     * use description if not supply the default 'Surcharge'
     *
     * @param $salesEntity
     *
     * @return Fooman_Surcharge_Helper_Data
     */
    protected function _getDescription($salesEntity)
    {
        $label = $salesEntity->getFoomanSurchargeDescription()
            ? $salesEntity->getFoomanSurchargeDescription()
            : Mage::helper('surcharge')->__('Surcharge');
        return Mage::helper('surcharge/compatibility')->escapeHtmlByVersion($label);
    }

    /**
     * change payment method title with added surcharge
     * observes event payment_method_is_active
     *
     * @param $observer
     */
    public function paymentMethodIsActive($observer)
    {
        if (Mage::getStoreConfig('surcharge/fooman_surcharge_all/titleadjust')) {
            $result = $observer->getEvent()->getResult();
            if ($result->isAvailable) {
                $methodInstance = $observer->getEvent()->getMethodInstance();

                $surchargeTotal = Mage::getModel('surcharge/quote_address_total_surcharge');
                $quote = $observer->getEvent()->getQuote();
                if ($quote) {
                    $surcharge = $this->_calcSurchargePreview($quote, $methodInstance->getCode());
                    if ($surcharge && $surcharge->getBaseAmount() != 0 && !isset($result->foomanSurchargeAdjusted)) {
                        $formattedAmount = $this->_getFormattedAmount($quote, $surcharge->getAmount());
                        Mage::app()->getStore($methodInstance->getStoreId())->setConfig(
                            'payment/' . $methodInstance->getCode() . '/title',
                            $this->_replaceTitle(
                                $methodInstance->getConfigData('title'),
                                $formattedAmount,
                                $surcharge->getDescription()
                            )
                        );
                        $result->foomanSurchargeAdjusted = true;
                    }
                }
            }
        }
    }

    protected function _calcSurchargePreview($quote, $methodCode)
    {
        $surchargeTotal = Mage::getModel('surcharge/quote_address_total_surcharge');
        /* @var $quote Mage_Sales_Model_Quote */
        foreach ($quote->getAddressesCollection() as $address) {
            if (!$address->getAllItems()) {
                continue;
            }
            $surcharge = $surchargeTotal->surchargeCalculateOnly(
                $address, $quote, $methodCode
            );

            if (Mage::helper('surcharge')->displayIncludeTaxCart()) {
                $surcharge->setBaseAmount($surcharge->setBaseAmount() + $surcharge->getBaseTaxAmount());
                $surcharge->setAmount($surcharge->setAmount() + $surcharge->getTaxAmount());
            }
            //only add surcharge once
            if ($surcharge->getBaseAmount() != 0) {
                return $surcharge;
            }
        }
        return false;
    }

    public function adjustPaypalTitle($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block->getTemplate() == 'paypal/payment/mark.phtml'
            && Mage::getStoreConfig('surcharge/fooman_surcharge_all/titleadjust')
        ) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ($quote) {
                $surcharge = $this->_calcSurchargePreview($quote, 'paypal_standard');
                $transport = $observer->getEvent()->getTransport();
                if ($transport && $surcharge && $surcharge->getBaseAmount()) {
                    $transport->setHtml(
                        $this->_replaceTitle(
                            $observer->getEvent()->getTransport()->getHtml(),
                            $this->_getFormattedAmount($quote, $surcharge->getAmount()),
                            Mage::helper('surcharge/compatibility')->escapeHtmlByVersion(
                                $surcharge->getDescription()
                            ),
                            false
                        )
                    );
                }
            }
        }
    }

    /**
     * format amount with currency
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $amount
     *
     * @return string
     */
    protected function _getFormattedAmount(Mage_Sales_Model_Quote $quote, $amount)
    {
        if ($amount == 0) {
            return '';
        }
        $amountCurrency = $quote->getStore()->formatPrice($amount, false);
        return sprintf('%s', $amountCurrency);
    }

    /**
     * apply surcharge format
     *
     * @param      $title
     * @param      $formattedAmount
     * @param      $description
     *
     * @param bool $escape
     *
     * @return mixed
     */
    protected function _replaceTitle($title, $formattedAmount, $description, $escape = true)
    {
        $search = array('{TITLE}', '{AMOUNT}', '{DESCRIPTION}');
        $replace = array($title, $formattedAmount, $description);
        $format = Mage::getStoreConfig('surcharge/fooman_surcharge_all/titleformat');
        $helper = Mage::helper('core');
        if ($escape) {
            if (method_exists($helper, 'escapeHtml')) {
                return Mage::helper('core')->escapeHtml(str_replace($search, $replace, $format));
            } else {
                return Mage::helper('core')->htmlEscape(str_replace($search, $replace, $format));
            }
        } else {
            return str_replace($search, $replace, $format);
        }
    }
}
