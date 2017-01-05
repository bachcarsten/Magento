<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'price', 'is_filterable_in_search', '1');

$installer->endSetup();