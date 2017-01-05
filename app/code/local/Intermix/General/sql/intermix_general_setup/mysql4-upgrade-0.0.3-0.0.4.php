<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->updateAttribute('catalog_category', 'head_image', 'is_required',0);

$installer->endSetup();