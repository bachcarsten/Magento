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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Comments extends Mage_Checkout_Block_Onepage_Abstract
{
    public function canShow()
    {
        if (!Mage::helper('aw_onestepcheckout/config')->isCommments()) {
            return false;
        }
        return true;
    }

    public function isDDANInstalled()
    {
        if (!Mage::helper('core')->isModuleEnabled('AW_Deliverydate')) {
            return false;
        }
        return true;
    }

    public function getSaveFormValuesUrl()
    {
        return Mage::getUrl('aw_onestepcheckout/ajax/saveFormValues');
    }
}