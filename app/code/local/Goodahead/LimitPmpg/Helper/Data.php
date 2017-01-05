<?php

class Goodahead_LimitPmpg_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     *
     * Enter description here ...
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Customer_Model_Group
     */

    public function getQuoteCustomerGroup(Mage_Sales_Model_Quote $quote)
    {
        if (
            $quote->hasData('customer_group')
            && $quote->getData('customer_group') instanceof Mage_Customer_Model_Group
        ) {
            return $quote->getData('customer_group');
        }
        $groupId = $quote->getCustomerGroupId() ? $quote->getCustomerGroupId() : 0;
        $group = Mage::getModel('customer/group')->load($groupId);
        if ($group->getId() === null) {
            $group->load(0);
        }
        $quote->setData('customer_group', $group);
        return $group;
    }
}