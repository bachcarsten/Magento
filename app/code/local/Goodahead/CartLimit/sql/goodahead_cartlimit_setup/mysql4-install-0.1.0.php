<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;


$installer->startSetup();
$installer->run(<<<SQL
CREATE TABLE  {$installer->getTable('goodahead_cartlimit/group')} (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
    `customer_group_id` SMALLINT( 3 ) UNSIGNED NOT NULL ,
    `cartlimit` SMALLINT (3) UNSIGNED NULL ,
    PRIMARY KEY (  `id` )
) ENGINE = InnoDB ;
SQL
);

$installer->run(<<<SQL
ALTER TABLE {$this->getTable('goodahead_cartlimit/group')}
    ADD CONSTRAINT `FK_GOODAHEAD_CARTLIMIT_CUTOMER_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES {$this->getTable('customer_group')} (`customer_group_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
SQL
);

$installer->endSetup();