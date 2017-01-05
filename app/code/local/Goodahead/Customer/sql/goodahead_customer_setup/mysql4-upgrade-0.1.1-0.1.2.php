<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $this->getTable('sales/order_grid'),
    'customer_group_id',
    "smallint(5) unsigned NOT NULL default 0"
);

$select = $this->getConnection()->select();
$select->join(
    array('customer'=>$this->getTable('customer/entity')),
    'customer.entity_id = order_grid.customer_id',
    array('customer_group_id' => "group_id")
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$installer->endSetup();