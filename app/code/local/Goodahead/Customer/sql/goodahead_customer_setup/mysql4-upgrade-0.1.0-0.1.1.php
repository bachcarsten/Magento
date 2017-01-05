<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    'customer_company',
    "TEXT not null default ''"
);

$select = $this->getConnection()->select();
$select->join(
    array('address'=>$this->getTable('sales/order_address')),
    $this->getConnection()->quoteInto(
        'address.parent_id = order_grid.entity_id AND address.address_type = ?',
        Mage_Sales_Model_Quote_Address::TYPE_BILLING
    ),
    array('customer_company' => "company")
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$installer->endSetup();