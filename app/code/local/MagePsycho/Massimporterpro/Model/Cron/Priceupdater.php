<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_Cron_Priceimporter
{
    const XML_PATH_CRON_ENABLED    = 'magepsycho_massimporterpro/price_settings/enable_cron';
    const XML_PATH_CRON_EXPR       = 'crontab/jobs/massimporterpro_price_importer/schedule/cron_expr';
    const XML_PATH_ERROR_TEMPLATE  = 'magepsycho_massimporterpro/price_settings/error_email_template';
    const XML_PATH_ERROR_IDENTITY  = 'magepsycho_massimporterpro/price_settings/error_email_identity';
    const XML_PATH_ERROR_RECIPIENT = 'magepsycho_massimporterpro/price_settings/error_email';

    const DEFAULT_PRICE_IMPORT_DIR = 'var/magepsycho/massimporterpro/cron/price_importer';

    public function scheduledPriceImporter()
    {
        $helper = Mage::helper('magepsycho_massimporterpro');

        // check if scheduled cron update is enabled
        if (!Mage::getStoreConfigFlag(self::XML_PATH_CRON_ENABLED)) {
            $helper->log('cron-diabled');
            return;
        }
        $helper->log('cron-enabled');

        $errors = array();
        try {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
            $data                            = array();
            $data['delimiter']               = $helper->getConfig('delimiter', 'data_format');
            $data['enclosure']               = $helper->getConfig('enclosure', 'data_format');
            $data['price_rounding']          = $helper->getConfig('price_rounding', 'price_settings');
            $data['rounding_nearest']          = $helper->getConfig('rounding_nearest', 'price_settings');
            $data['tier_price_import_type']  = $helper->getConfig('tier_price_import_type', 'price_settings');
            $data['group_price_import_type'] = $helper->getConfig('group_price_import_type', 'price_settings');
            $data['reindex_after_import']    = $helper->getConfig('reindex_after_import', 'price_settings');
            $data['import_csv_dir']          = $helper->getConfig('import_csv_dir', 'price_settings');
            $data['process_csv_type']          = $helper->getConfig('process_csv_type', 'price_settings');

            $reindexAfterImport = $data['reindex_after_import'];
            $delimiter          = $data['delimiter'];
            $enclosure          = $data['enclosure'];
            $importFileType     = 'csv';


            if (strlen($data['import_csv_dir']) < 2) {
                $data['import_csv_dir'] = 'var/magepsycho/massimporterpro/price_importer/cron'; #Default Import Dir
            }
            $importCsvDir = Mage::getBaseDir() . DS . str_replace('/', DS, trim($data['import_csv_dir'], DS));
            $csvFiles     = glob($importCsvDir . DS . '*.csv');

            foreach ($csvFiles as $_csvFile):
                $importFilePath = $_csvFile;
                $importFile     = basename($importFilePath);

                $fileOptions   = array(
                    'source'    => $importFilePath,
                    'delimiter' => $delimiter,
                    'enclosure' => $enclosure,
                );
                $importOptions = array(
                    'tier_price_import_type'  => $data['tier_price_import_type'],
                    'group_price_import_type' => $data['group_price_import_type'],
                    'price_rounding'          => $data['price_rounding'],
                    'rounding_nearest'        => $data['rounding_nearest'],
                );

                $importData = MagePsycho_Massimporterpro_Model_Import_Adapter::factory($importFileType, $fileOptions);

                $priceUpdater  = Mage::getModel('magepsycho_massimporterpro/priceimporter');
                $importStartAt = $helper->getMicroTime();
                $priceUpdater->importData($importData, $importOptions);
                $importStopAt = $helper->getMicroTime();
                $resultData   = $priceUpdater->getResults();
                $priceUpdater->log($resultData, 'cron');
                /************************\ END IMPORT \************************/

                $model                    = Mage::getModel('magepsycho_massimporterpro/massimporterpro');
                $totalCountRows           = $priceUpdater->getTotalCount();
                $successCountRows         = $priceUpdater->getSuccessCount();
                $errorCountRows           = $priceUpdater->getErrorCount();
                $skipCountRows            = $priceUpdater->getSkipCount();
                $data['created_at']       = now();
                $data['updated_at']       = now();
                $data['import_type']      = 'price_importer';
                $data['import_via']       = 'cron';
                $data['import_file_type'] = $importFileType;
                $data['import_file']      = '/' . $importFile;
                $data['log_data']         = serialize($resultData);
                $data['total_rows']       = $totalCountRows;
                $data['success_rows']     = $successCountRows;
                $data['error_rows']       = $errorCountRows;
                $data['skipped_rows']     = $skipCountRows;
                $data['import_duration']  = (float)number_format($importStopAt - $importStartAt, 6);
                $helper->log('cron::$data::' . print_r($data, true));
                $model->setData($data);
                try {
                    $model->save();

                    //perform post file processings
                    $postFileAction = $data['process_csv_type'];
                    if ($postFileAction == MagePsycho_Massimporterpro_Model_System_Config_Source_Filepostprocess::FILE_PROCESS_TYPE_ARCHIVE) {
                        // copy imported files in archive dir
                        $archiveDir = Mage::getBaseDir() . DS . str_replace('/', DS, trim('var/magepsycho/massimporterpro/price_importer/archive', DS));
                        if(!is_dir($archiveDir)){
                            mkdir($archiveDir, 0777, true);
                        }
                        @rename($importFilePath, $archiveDir . DS . $importFile);
                        $helper->log('FilePostProcess::MoveToArchive');
                    } else if ($postFileAction == MagePsycho_Massimporterpro_Model_System_Config_Source_Filepostprocess::FILE_PROCESS_TYPE_DELETE) {
                        @unlink($importFilePath);
                        $helper->log('FilePostProcess::DELETE');
                    }


                    if ($reindexAfterImport) {
                        try {
                            Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price')->reindexAll();
                        } catch (Exception $e) {
                            $errors[] = Mage::helper('magepsycho_massimporterpro')->__('There was an error while rebuilding Product prices index:<br />' . $e->getMessage());
                        }
                    }

                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }

            endforeach;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        $helper->log('CRON::ERRORS::' . print_r($errors, true));

        if (count($errors) && Mage::getStoreConfig(self::XML_PATH_ERROR_RECIPIENT)) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            $emailTemplate = Mage::getModel('core/email_template');
            /* @var $emailTemplate Mage_Core_Model_Email_Template */
            $emailTemplate->setDesignConfig(array('area' => 'backend'))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_ERROR_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_IDENTITY),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_RECIPIENT),
                    null,
                    array('warnings' => join("\n", $errors))
                );

            $translate->setTranslateInline(true);
        }
    }
}