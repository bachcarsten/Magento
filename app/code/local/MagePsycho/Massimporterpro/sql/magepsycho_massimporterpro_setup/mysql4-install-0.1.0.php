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
CREATE TABLE {$this->getTable('mp_massimporterpro_logs')} (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `import_type` varchar(255) NOT NULL,
  `import_file_type` varchar(255) NOT NULL,
  `import_file` varchar(255) NOT NULL DEFAULT '',
  `log_data` longtext NOT NULL,
  `total_rows` int(11) NOT NULL,
  `success_rows` int(11) NOT NULL,
  `error_rows` int(11) NOT NULL,
  `skipped_rows` int(11) NOT NULL,
  `import_duration` float(10,6) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();