<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('salesrule/rule'), 'discount_attribute', 'VARCHAR(255)');

$installer->endSetup();