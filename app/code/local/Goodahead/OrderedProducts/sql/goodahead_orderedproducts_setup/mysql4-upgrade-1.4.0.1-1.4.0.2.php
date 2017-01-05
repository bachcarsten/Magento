<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `{$this->getTable('goodahead_orderedproducts/products')}` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`customer_id`  int(10) UNSIGNED NOT NULL ,
`product_id`  int(10) UNSIGNED NOT NULL ,
`position`  int(10) DEFAULT 0 ,
PRIMARY KEY (`id`)
);

ALTER TABLE `{$this->getTable('goodahead_orderedproducts/products')}`
  ADD CONSTRAINT `FK_GOODAHEAD_ORDEREDPRODUCTS_CUSTOMER_ID` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `{$this->getTable('goodahead_orderedproducts/products')}`
  ADD CONSTRAINT `FK_GOODAHEAD_ORDEREDPRODUCTS_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();
