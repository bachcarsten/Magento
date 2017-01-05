<?php

/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$this->startSetup();

$this->getConnection()->addColumn(
    $this->getTable('goodahead_authorizenet/customer'),
    'merchant_id',
    'varchar(43) DEFAULT NULL'
);

$this->endSetup();