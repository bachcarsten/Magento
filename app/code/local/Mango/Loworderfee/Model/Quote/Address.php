<?php

class Mango_Loworderfee_Model_Quote_Address extends Mage_Sales_Model_Quote_Address {

    /**
     * Validate minimum amount
     *
     * @return bool
     */
    public function validateMinimumAmount() {
        $storeId = $this->getQuote()->getStoreId();
        if (!Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId)) {
            return true;
        }

        /* added to check customer groups */
        /* check if customer validation is enabled */
        $customer_group_validate = Mage::getStoreConfig('sales/minimum_order/low_order_fee_customer_group_enable', $storeId);
        /* get customer groups */
        if ($customer_group_validate) {
            $customer_groups = Mage::getStoreConfig('sales/minimum_order/low_order_fee_customer_group', $storeId);
            $customer_groups = explode(",", $customer_groups);
            /* get quote customer group */
            $group_id = $this->getQuote()->getCustomerGroupId();
            /* if quote customer group not in list, return true */
            if (!in_array($group_id, $customer_groups))
                return true;
        }
        
        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        } elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != self::TYPE_SHIPPING) {
            return true;
        }

        $amount = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        $_reference = Mage::getStoreConfig('sales/minimum_order/low_order_fee_reference', $storeId);
        $_amount_to_compare = 0;

        switch ($_reference) {
            case Mango_Loworderfee_Model_System_Config_Source_Reference::BaseSubtotal:
                $_amount_to_compare = $this->getBaseSubtotal();
                break;
            case Mango_Loworderfee_Model_System_Config_Source_Reference::BaseSubtotalWithDiscount:
                $_amount_to_compare = $this->getBaseSubtotalWithDiscount();
                break;
            case Mango_Loworderfee_Model_System_Config_Source_Reference::SubtotalInclTax:
                $_amount_to_compare = $this->getSubtotalInclTax() ;
                break;
                case Mango_LowOrderFee_Model_System_Config_Source_Reference::SubtotalInclTaxWithDiscount:
                $_amount_to_compare = $this->getSubtotalInclTax() + $this->getDiscountAmount();
                break;
        }

        if ($_amount_to_compare < $amount) {
            return false;
        }

        return true;
    }

}