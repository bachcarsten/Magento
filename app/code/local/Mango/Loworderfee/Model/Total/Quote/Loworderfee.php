<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @package     Mage_Loworderfee
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mango_Loworderfee_Model_Total_Quote_Loworderfee extends Mage_Sales_Model_Quote_Address_Total_Abstract{

    /**
     * Weee module helper object
     *
     * @var Mage_Weee_Helper_Data
     */
    // protected $_helper;
    protected $_store;

    /**
     * Initialize Weee totals collector
     */
    public function __construct() {
        $this->setCode('loworderfee');
    }

    /**
     * Add discount total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        parent::fetch($address);
        $amount = 0;
        $quote = $address->getQuote();
        $store = $quote->getStore();
        $_lof_active = Mage::getStoreConfig('sales/minimum_order/low_order_fee_active', $store->getId());    
        if (!$address->validateMinimumAmount() && $_lof_active ) {
            $amount = Mage::getStoreConfig('sales/minimum_order/low_order_fee', $store->getId());
            $_method =Mage::getStoreConfig('sales/minimum_order/low_order_fee_method', $store->getId());
            if ($_method == "percentage"){
                $amount = ($address->getBaseSubtotalWithDiscount() * $amount / 100);
            }
        }
        if ($amount != 0) {
            $address->addTotal(array(
                'code' => "loworderfee",
                'title' => Mage::getStoreConfig('sales/minimum_order/low_order_fee_title', $store->getId()),
                'value' => $amount
            ));
        }
        return $this;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store) {
        $_lof_active = Mage::getStoreConfig('sales/minimum_order/low_order_fee_active', $store->getId());
        if ($_lof_active) $config['before'][] = 'shipping';

        return $config;
    }

    /**
     * Collect tax totals for quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $this->_store = $address->getQuote()->getStore();
        $address->setBaseLoworderfee(0);
        $address->setLoworderfee(0);
        /* $address->setLoworderfeeTaxAmount(0);
          $address->setBaseLoworderfeeTaxAmount(0); */

        $quote = $address->getQuote();
        $store = $quote->getStore();

        $_lof_active = Mage::getStoreConfig('sales/minimum_order/low_order_fee_active', $store->getId());    

        parent::collect($address);
        $this->_address = $address;
        if (!$address->validateMinimumAmount() && $_lof_active) {
            $_fee = Mage::getStoreConfig('sales/minimum_order/low_order_fee', $store->getId());

            $_method =Mage::getStoreConfig('sales/minimum_order/low_order_fee_method', $store->getId());
            
            if($_method == "percentage"){
                $_fee = ($address->getBaseSubtotalWithDiscount() * $_fee / 100);
            }
            
            $address->setTotalAmount('loworderfee', $_fee);
            $address->setBaseTotalAmount('loworderfee', $_fee);

            //$address->setLoworderfee($store->convertPrice($_fee,false));
            $address->setBaseLoworderfee($store->convertPrice($_fee, false));
            $address->setLoworderfee($store->convertPrice($_fee, false));
        }
        return $this;
    }
}
