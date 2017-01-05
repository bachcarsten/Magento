<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * One page checkout cart link
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mango_Loworderfee_Block_Checkout_Onepage_Link extends Mage_Checkout_Block_Onepage_Link {

    public function isDisabled() {

        $_x = true;
        $minOrderActive = Mage::getStoreConfigFlag('sales/minimum_order/active');
        $lowOrderFeeActive = Mage::getStoreConfigFlag('sales/minimum_order/low_order_fee_active');

        /* added first order validation */
        if ($minOrderActive) {
            $_min_order_validation = true;
            if ($lowOrderFeeActive) {
                return false;

            } else {
                $_x = !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
            }
        }


        return false; //$_x;
    }

    
}
