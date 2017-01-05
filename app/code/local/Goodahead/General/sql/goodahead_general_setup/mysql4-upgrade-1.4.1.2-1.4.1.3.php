<?php
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('goodahead_general/category_product_idx'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Entity ID')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Category ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Product ID')
    ->addIndex($installer->getIdxName($this->getTable('goodahead_general/category_product_idx'), array('category_id', 'product_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('category_id', 'product_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Goodahead Anchor Category Products Relations');
$installer->getConnection()->createTable($table);

/* @var $indexer Mage_Index_Model_Process */
$indexer = Mage::getModel('index/process')->load('goodahead_category_product', 'indexer_code');
$indexer
    ->setIndexerCode('goodahead_category_product')
    ->setStatus('require_reindex')
    ->save();

$installer->endSetup();