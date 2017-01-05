<?php

/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('goodahead_authorizenet/customer')}` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`login_id` varchar(32) NOT NULL,
	`customer_id` int UNSIGNED DEFAULT NULL,
	`profile_id` int UNSIGNED DEFAULT NULL,
	`prefix_id` varchar(32) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE (login_id, customer_id),
	UNIQUE (profile_id),
	INDEX(customer_id),
	FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `{$this->getTable('goodahead_authorizenet/payment')}` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`authorizenet_id` int UNSIGNED NOT NULL,
	`profile_id` int UNSIGNED NOT NULL,
	`type` varchar(32) NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`authorizenet_id`) REFERENCES `{$this->getTable('goodahead_authorizenet/customer')}` (`profile_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (authorizenet_id, profile_id)
) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `{$this->getTable('goodahead_authorizenet/shipping')}` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`authorizenet_id` int UNSIGNED NOT NULL,
	`profile_id` int UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`authorizenet_id`) REFERENCES `{$this->getTable('goodahead_authorizenet/customer')}` (`profile_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE (authorizenet_id, profile_id)
) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
");

$this->endSetup();