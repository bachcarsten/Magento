<?php

/**
 * Magento Commercial Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Commercial Edition License
 * that is available at: http://www.magentocommerce.com/license/commercial-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/commercial-edition
 */

/**
 * PayPal-specific model for shopping cart items and totals
 * The main idea is to accommodate all possible totals into PayPal-compatible 4 totals and line items
 */
class Mango_Loworderfee_Model_Paypal_Cart extends Mage_Paypal_Model_Cart {

    /**
     * (re)Render all items and totals
     */
    protected function _render() {

        parent::_render();

        
        if(Mage::getStoreConfig('sales/minimum_order/low_order_fee_active')){
        
        $_lof = false;
        $_shipping = false;
        // regular totals
        $shippingDescription = '';
        if ($this->_salesEntity instanceof Mage_Sales_Model_Order) {
            $_lof = $this->_salesEntity->getBaseLoworderfee();
            if ($_lof) {
                $shippingDescription = $this->_salesEntity->getShippingDescription();
                $this->_totals[self::TOTAL_SHIPPING] = $this->_salesEntity->getBaseShippingAmount() + $this->_salesEntity->getBaseLoworderfee();
                $_shipping = $this->_salesEntity->getBaseShippingAmount();
            }
        } else {

            $address = $this->_salesEntity->getIsVirtual() ?
                    $this->_salesEntity->getBillingAddress() : $this->_salesEntity->getShippingAddress();
            $_lof = $address->getBaseLoworderfee();
            if ($_lof) {
                $shippingDescription = $address->getShippingDescription();
                $this->_totals[self::TOTAL_SHIPPING] = $address->getBaseShippingAmount() + $address->getBaseLoworderfee();

                $_shipping = $address->getBaseShippingAmount();
            }
        }

        if ((float) $_lof) {
            $shippingItemId = $this->_renderTotalLineItemDescriptions(self::TOTAL_SHIPPING, $shippingDescription);
            //echo "loworderfee";
            if ($this->_isShippingAsItem && (float) $_shipping) {
                $this->addItem(Mage::helper('paypal')->__('Shipping'), 1, (float) $_shipping, $shippingItemId
                );
                $this->addItem(  Mage::getStoreConfig('sales/minimum_order/low_order_fee_title')    , 1, (float) $_lof, "loworderfee"
                );
            }

            $this->_validate();
            // if cart items are invalid, prepare cart for transfer without line items
            if (!$this->_areItemsValid) {
                $this->removeItem($shippingItemId);
                $this->removeItem("loworderfee");
            }
        }
        }
    }

}
