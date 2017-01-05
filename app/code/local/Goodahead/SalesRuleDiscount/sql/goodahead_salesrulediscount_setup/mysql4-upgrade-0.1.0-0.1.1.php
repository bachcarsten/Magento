<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('catalog_product', 'im_discount_percent', array(
        'group'             => 'Prices',
        'label'             => 'Discount Percent',
        'type'              => 'decimal',
        'input'             => 'text',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'used_in_product_listing' => true,
        'unique'            => false,
));

$installer->updateAttribute('catalog_product', 'im_discount_percent', 'is_used_for_promo_rules', 1);

$installer->endSetup();
