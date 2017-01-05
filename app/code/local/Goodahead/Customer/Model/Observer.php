<?php
class Goodahead_Customer_Model_Observer
{
    public function addColumnToResource($observer)
    {
        /* @var $resource Mage_Sales_Model_Resource_Order */
        $resource = $observer->getResource();

        $resource->addVirtualGridColumn(
            'customer_company',
            'sales/order_address',
            array('billing_address_id' => 'entity_id'),
            'company'
        );

//        $adapter       = $resource->getReadConnection();
//        $ifnullGroup   = $adapter->getIfNullSql('{{table}}.group_id', $adapter->quote(0));
//        $resource->addVirtualGridColumn(
//            'customer_group_id',
//            'customer/entity',
//            array('customer_id' => 'entity_id'),
//            $ifnullGroup
//        );
    }
}