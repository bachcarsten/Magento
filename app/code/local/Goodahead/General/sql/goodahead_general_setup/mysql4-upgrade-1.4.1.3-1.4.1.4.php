<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

/* @var $indexer Mage_Index_Model_Process */
$indexer = Mage::getModel('index/process')->load('goodahead_category_product', 'indexer_code');

if ($indexer->getStatus()) {
    $indexer->delete();
}

$installer->endSetup();