<?php

/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer = $this;

$installer->addAttribute('customer', 'promo_code', array(
    'label'        => 'Promo Code',
    'visible'      => true,
    'required'     => false,
    'type'         => 'varchar',
    'input'        => 'text',
));

$installer->getConnection()->addColumn($installer->getTable('customer/customer_group'), 'promo_code', 'VARCHAR(255) DEFAULT NULL');
$installer->getConnection()->addKey($installer->getTable('customer/customer_group'), 'UK_PROMO_CODE', array('promo_code'), 'unique');

$entityTypeId = $installer->getEntityTypeId('customer');
$select = $installer->getConnection()->select()
    ->from(
        array('main_table' => $installer->getTable('eav/form_type')),
        array('type_id'))
    ->where('code = ?', 'customer_account_create');
if ($row = $installer->getConnection()->fetchOne($select)) {
    $formTypeId = $row['code'];
    $installer->getConnection()->insert($installer->getTable('eav/form_fieldset'), array(
        'type_id'    => $formTypeId,
        'code'       => 'promo_code',
        'sort_order' => 1
    ));
    $fieldsetId = $installer->getConnection()->lastInsertId();
    $installer->getConnection()->insert($installer->getTable('eav/form_fieldset_label'), array(
        'fieldset_id' => $fieldsetId,
        'store_id'    => 0,
        'label'       => 'Promo Code'
    ));

    $installer->getConnection()->insert($installer->getTable('eav/form_element'), array(
        'type_id'       => $formTypeId,
        'fieldset_id'   => $fieldsetId,
        'attribute_id'  => $installer->getAttributeId($entityTypeId, 'promo_code'),
        'sort_order'    => 0
    ));
}
