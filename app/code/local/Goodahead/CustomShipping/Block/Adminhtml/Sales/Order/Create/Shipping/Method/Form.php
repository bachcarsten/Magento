<?php

class Goodahead_CustomShipping_Block_Adminhtml_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }

    public function getActiveMethodRate()
    {
        $orderPostData = $this->getRequest()->getPost('order');
        $rateCode = 'goodahead_ownprice_goodahead_ownprice';

        if( $this->getShippingMethod() == $rateCode
            && is_array($orderPostData)
            && isset($orderPostData['shipping_method_price'])) {

            $price = floatval($orderPostData['shipping_method_price']);
            $rates = $this->getShippingRates();
            if (is_array($rates)) {
                foreach ($rates as $group) {
                    foreach ($group as $code => $rate) {
                        if ($rate->getCode() == $rateCode) {
                            $rate->setPrice($price);
                            if (!empty($orderPostData['shipping_method_carrier_title'])) {
                                $rate->setCarrierTitle($orderPostData['shipping_method_carrier_title']);
                            }
                            if (!empty($orderPostData['shipping_method_method_title'])) {
                                $rate->setMethodTitle($orderPostData['shipping_method_method_title']);
                            }

                            $rate->save();

                            $this->getQuote()->getShippingAddress()->setShippingAmount($price);
                            $this->getQuote()->getShippingAddress()->save();
                            $this->getQuote()->save();
                            $this->getQuote()->setTotalsCollectedFlag(false);
                            $this->getQuote()->collectTotals();
                            /*$this->getQuote()->getShippingAddress()->setTotalAmount('grand',
                                $this->getQuote()->getShippingAddress()->getTotalAmount('grand') -
                                $this->getQuote()->getShippingAddress()->getTotalAmount('subtotal')
                            );*/
                            //$this->getQuote()->getShippingAddress()->setBaseShippingAmount($price);
                        }
                    }
                }
            }
        }

        return parent::getActiveMethodRate();
    }
}