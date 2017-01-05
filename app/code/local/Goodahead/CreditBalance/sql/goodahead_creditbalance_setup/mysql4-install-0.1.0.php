<?php
$installer = $this;

$installer->startSetup();

$installer->run(<<<SQL
DROP TABLE IF EXISTS {$this->getTable('goodahead_creditbalance/credit')};
CREATE TABLE {$this->getTable('goodahead_creditbalance/credit')} (
    `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
    `customer_id` int(5) unsigned NOT NULL,
    `balance` decimal(10,4) NULL,
    `enabled` int(1) unsigned default 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB default charset=utf8;
SQL
);

$installer->run(<<<SQL
ALTER TABLE {$this->getTable('goodahead_creditbalance/credit')}
    ADD CONSTRAINT `FK_GOODAHEAD_CREDITBALANCE_CREDIT_CUSTOMER` FOREIGN KEY (`customer_id`)
    REFERENCES {$this->getTable('customer/entity')} (`entity_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE;
SQL
);


$installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'account_credit', 'decimal(10,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'base_account_credit', 'decimal(10,4) NULL');

$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'account_credit', 'decimal(10,4) NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'base_account_credit', 'decimal(10,4) NULL');

$setup = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
$setup->addAttribute('quote', 'account_credit', array('type'=>'static'));
$setup->addAttribute('quote', 'base_account_credit', array('type'=>'static'));
$setup->addAttribute('order', 'account_credit', array('type'=>'static'));
$setup->addAttribute('order', 'base_account_credit', array('type'=>'static'));

$installer->endSetup();
