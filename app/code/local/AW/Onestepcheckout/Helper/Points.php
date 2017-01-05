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

class AW_Onestepcheckout_Helper_Points extends Mage_Core_Helper_Data
{
    protected $_pointsBlock;
    /**
     * Check is Points & Rewards enabled
     */
    public function isPointsEnabled()
    {
        if ($this->isModuleEnabled('AW_Points')) {
            if (Mage::helper('points/config')->isPointsEnabled()) {
                return true;
            }
        }
        return false;
    }

    public function isPointsSectionAvailable()
    {
        return $this->_getPointsBlock()->pointsSectionAvailable();
    }

    public function getPointsUnitName()
    {
        return Mage::helper('points/config')->getPointUnitName();
    }

    public function getSummaryForCustomer()
    {
        return $this->_getPointsBlock()->getSummaryForCustomer();
    }

    public function getAppliedPointsAmount()
    {
        // hack for AW_Points
        $appliedPointsAmount = min(
            (int)Mage::getSingleton('checkout/session')->getData('points_amount'),
            $this->getNeededPoints(),
            $this->getLimitedPoints()
        );
        Mage::getSingleton('checkout/session')->setData('points_amount', $appliedPointsAmount);
        return $appliedPointsAmount;
    }

    public function getAppliedPointsDiscountAmount()
    {
        $result = 0;
        if ($this->isPointsEnabled()) {
            try {
                $result = Mage::getModel('points/rate')
                    ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                    ->exchange($this->getAppliedPointsAmount());
            } catch (Exception $ex) {}
        }
        return $result;
    }

    public function getMoneyForPoints()
    {
        return $this->_getPointsBlock()->getMoneyForPoints();
    }

    public function getNeededPoints() {
        $subtotalWithDiscount = Mage::getSingleton('checkout/session')->getQuote()->getData('base_subtotal_with_discount');
        $rafDiscount = Mage::helper('aw_onestepcheckout/referafriend')->getAppliedDiscountAmount();
        return Mage::helper('points')->getNeededPoints($subtotalWithDiscount - $rafDiscount);
    }

    public function getLimitedPoints() {
        return $this->_getPointsBlock()->getLimitedPoints();
    }

    protected function _getPointsBlock()
    {
        if (!$this->_pointsBlock) {
            $this->_pointsBlock = Mage::app()->getLayout()->createBlock('points/checkout_onepage_payment_methods');
        }
        return $this->_pointsBlock;
    }
}
