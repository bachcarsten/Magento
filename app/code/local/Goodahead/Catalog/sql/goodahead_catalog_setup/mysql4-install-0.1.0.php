<?php
$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer->startSetup();

$installer->addAttribute('catalog_product', 'recipes', array(
    'user_defined'  => 0,
    'type'          => 'text',
    'input'         => 'textarea',
    'label'         => 'Recipes',
    'visible'       => true,
    'required'      => false,
    'group'         => 'General',
));

$installer->updateAttribute('catalog_product', 'recipes', 'is_wysiwyg_enabled', 1);

$installer->endSetup();