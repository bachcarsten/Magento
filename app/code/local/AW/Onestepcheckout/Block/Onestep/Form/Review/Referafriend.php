<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onestepcheckout
 * @version    1.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onestepcheckout_Block_Onestep_Form_Review_Referafriend extends Mage_Checkout_Block_Onepage_Abstract
{
    public function canShow()
    {
        if (Mage::helper('aw_onestepcheckout/referafriend')->isReferafriendEnabled()) {
            return true;
        }
        return false;
    }

    public function isDiscountSectionAvailable()
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->isDiscountSectionAvailable();
    }

    public function getReservedAmount()
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getReservedAmount();
    }

    public function getAppliedAmount()
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getAppliedAmount();
    }

    public function getAvailableAmount($toPrice = false)
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getAvailableAmount($toPrice);
    }

    public function getNumericAmount()
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getNumericAmount();
    }

    public function getMaxDiscountPercent()
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getMaxDiscountPercent();
    }

    public function getMaxDiscount($toPrice = false)
    {
        return Mage::helper('aw_onestepcheckout/referafriend')->getMaxDiscount($toPrice);
    }

    public function getApplyDiscountAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/applyRafDiscount', array('_secure'=>true));
    }
}