<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'category_id', 'int(10) DEFAULT NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'category_id', 'int(10) DEFAULT NULL');

$installer->endSetup();