<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;


$installer->startSetup();
$installer->run("
CREATE TABLE  {$installer->getTable('goodahead_limitpmpg_group')} (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`customer_group_id` SMALLINT( 3 ) UNSIGNED NOT NULL ,
	`method_code` VARCHAR( 75 ) NOT NULL ,
	PRIMARY KEY (  `id` )
) ENGINE=InnoDB ;
");

$installer->run("
ALTER TABLE {$this->getTable('goodahead_limitpmpg_group')}
    ADD CONSTRAINT `FK_GOODAHEAD_LIMITPMPG_CUTOMER_GROUP` FOREIGN KEY (`customer_group_id`)
    REFERENCES {$this->getTable('customer_group')} (`customer_group_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
");

$installer->endSetup();