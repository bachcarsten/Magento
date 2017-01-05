<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_category', 'head_image', array(
                            'backend'   => 'catalog/category_attribute_backend_image',
                            'type'      => 'varchar',
                            'input'     => 'image',
                            'label'     => Mage::helper('intermix_general')->__('Header Image'),
                        ));

$installer->endSetup();