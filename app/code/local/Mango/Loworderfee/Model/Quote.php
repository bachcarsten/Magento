<?php
class Mango_Loworderfee_Model_Quote extends Mage_Sales_Model_Quote {

    /**
     * Init resource model
     */
    protected function _construct() {
        $this->_init('sales/quote');
    }

    public function validateMinimumAmount($multishipping = false) {
        $storeId = $this->getStoreId();

        $minOrderActive = Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId);
        $lowOrderFeeActive = Mage::getStoreConfigFlag('sales/minimum_order/low_order_fee_active', $storeId);

        /* added to check customer groups */
        /* check if customer validation is enabled */
        $customer_group_validate = Mage::getStoreConfig('sales/minimum_order/low_order_fee_customer_group_enable', $storeId);
        /* get customer groups */
        if ($customer_group_validate) {
            $customer_groups = Mage::getStoreConfig('sales/minimum_order/low_order_fee_customer_group', $storeId);
            $customer_groups = explode(",", $customer_groups);
            /* get quote customer group */
            $group_id = $this->getCustomerGroupId();
            /* if quote customer group not in list, return true */
            if (!in_array($group_id, $customer_groups))
                return true;
        }

        if ($minOrderActive) {
            if ($lowOrderFeeActive) {
                return true;
            }
            return parent::validateMinimumAmount($multishipping);
        }
        return true;
    }

    public function checkMinimumAmount($multishipping = false) {
        return parent::validateMinimumAmount($multishipping);
    }
}