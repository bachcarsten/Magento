<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;

$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('mp_massimporterpro_logs')};
");

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('magepsycho_massimporterpro_logs')};
CREATE TABLE {$this->getTable('magepsycho_massimporterpro_logs')} (
  `entity_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Log ID',
  `import_type` varchar(255) NOT NULL COMMENT 'Import Type',
  `import_file_type` varchar(255) NOT NULL COMMENT 'Import File Type',
  `import_file` varchar(255) NOT NULL DEFAULT '' COMMENT 'Import File',
  `import_via` ENUM('web','cron', 'shell') DEFAULT 'web' NOT NULL COMMENT 'Import Via',
  `log_data` longtext NOT NULL COMMENT 'Log Data',
  `total_rows` int(11) NOT NULL COMMENT 'Total Rows',
  `success_rows` int(11) NOT NULL COMMENT 'Success Rows',
  `error_rows` int(11) NOT NULL COMMENT 'Error Rows',
  `skipped_rows` int(11) NOT NULL COMMENT 'Skipped Rows',
  `import_duration` float(10,6) NOT NULL COMMENT 'Import Duration',
  `status` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Status',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Creation At',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Updated At',
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();