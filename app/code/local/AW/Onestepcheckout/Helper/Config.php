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

class AW_Onestepcheckout_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * "Enable One Step Checkout" from system config
     */
    const GENERAL_IS_ENABLED = 'aw_onestepcheckout/general/is_enabled';

    /**
     * "Always Use Billing Address as a Shipping Address" from system config
     */
    const GENERAL_IS_USE_BILLING_AS_SHIPPING = 'aw_onestepcheckout/general/is_use_billing_as_shipping';

    /**
     * "One Step Checkout Page Title" from system config
     */
    const GENERAL_TITLE = 'aw_onestepcheckout/general/title';

    /**
     * "One Step Checkout Description" from system config
     */
    const GENERAL_DESCRIPTION = 'aw_onestepcheckout/general/description';

    /**
     * "One Step Checkout Description" from system config
     */
    const GENERAL_IS_BLOCK_NUMBERING = 'aw_onestepcheckout/general/is_block_numbering';

    /**
     * "Company" from system config
     */
    const EXCLUDE_INCLUDE_IS_COMPANY = 'aw_onestepcheckout/exclude_include_fields/is_company';

    /**
     * "Fax" from system config
     */
    const EXCLUDE_INCLUDE_IS_FAX = 'aw_onestepcheckout/exclude_include_fields/is_fax';

    /**
     * "Discount Code" from system config
     */
    const EXCLUDE_INCLUDE_IS_COUPON = 'aw_onestepcheckout/exclude_include_fields/is_coupon';

    /**
     * "Comments" from system config
     */
    const EXCLUDE_INCLUDE_IS_COMMENTS = 'aw_onestepcheckout/exclude_include_fields/is_comments';

    /**
     * "Newsletter Subscription" from system config
     */
    const EXCLUDE_INCLUDE_IS_NEWSLETTER = 'aw_onestepcheckout/exclude_include_fields/is_newsletter';

    /**
     * "Related Products" from system config
     */
    const EXCLUDE_INCLUDE_IS_RELATED_PRODUCTS = 'aw_onestepcheckout/exclude_include_fields/is_related_products';

    /**
     * "Show Related Products according to rule" from system config
     */
    const EXCLUDE_INCLUDE_AUTORELATED_RULE_ID = 'aw_onestepcheckout/exclude_include_fields/autorelated_rule_id';

    /**
     * Display "Apply Coupon" Button from system config
     */
    const FRONTEND_DISPLAY_APPLY_COUPON_BUTTON = 'aw_onestepcheckout/frontend/display_apply_coupon_button';

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabled($store = null)
    {
        $isModuleEnabled = $this->isModuleEnabled();
        $isModuleOutputEnabled = $this->isModuleOutputEnabled();
        return $isModuleOutputEnabled && $isModuleEnabled && Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isUseBillingAsShipping($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_IS_USE_BILLING_AS_SHIPPING, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCheckoutTitle($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_TITLE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCheckoutDescription($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_DESCRIPTION, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isBlockNumbering($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_IS_BLOCK_NUMBERING, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isCompany($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_IS_COMPANY, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isFax($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_IS_FAX, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isCoupon($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_IS_COUPON, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isCommments($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_IS_COMMENTS, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isNewsletter($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_IS_NEWSLETTER, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isRelatedProducts($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_IS_RELATED_PRODUCTS, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getAutomaticRelatedRuleId($store = null)
    {
        return Mage::getStoreConfig(self::EXCLUDE_INCLUDE_AUTORELATED_RULE_ID, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isApplyCouponButton($store = null)
    {
        return Mage::getStoreConfig(self::FRONTEND_DISPLAY_APPLY_COUPON_BUTTON, $store);
    }
}