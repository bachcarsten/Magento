<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->addAttribute('customer', 'intermix_id', array(
            'required'   => false,
            'sort_order' => '0',
            'label'      => 'Internal Accouting ID', 
            'input'      => 'text',
            'type'       => 'varchar',
        )
);