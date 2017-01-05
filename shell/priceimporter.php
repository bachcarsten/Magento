<?php
require_once 'abstract.php';

/**
 *
 * Magento Price Update Shell Script
 *
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Shell_Priceimporter extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($csvFilePath = $this->getArg('import')) {
            //check if file path is relative
            $csvAbsPath = Mage::getBaseDir() . DS . str_replace('/', DS, $csvFilePath);
            $processFilePath = '';
            if (is_file($csvAbsPath)) {
                $processFilePath = $csvAbsPath;
            } else if(is_file($csvFilePath)) {
                $processFilePath = $csvFilePath;
            } else {
                echo "Import Error: File '$csvFilePath' doesn't exist.\n";
                return 1;
            }
            echo "Price Import Started...\n";
            /************************\ START IMPORT \************************/
            set_time_limit(0);
            ini_set('memory_limit', '1024M');
            $helper = Mage::helper('magepsycho_massimporterpro');
            $errors                          = array();
            $data                            = array();
            $data['delimiter']               = $helper->getConfig('delimiter', 'data_format');
            $data['enclosure']               = $helper->getConfig('enclosure', 'data_format');
            $data['price_rounding']          = $helper->getConfig('price_rounding', 'price_settings');
            $data['rounding_nearest']        = $helper->getConfig('rounding_nearest', 'price_settings');
            $data['tier_price_import_type']  = $helper->getConfig('tier_price_import_type', 'price_settings');
            $data['group_price_import_type'] = $helper->getConfig('group_price_import_type', 'price_settings');
            $data['reindex_after_import']    = $helper->getConfig('reindex_after_import', 'price_settings');

            $reindexAfterImport = $data['reindex_after_import'];
            if ($this->getArg('reindex')) {
                $reindexAfterImport = true;
            }
            $delimiter          = $data['delimiter'];
            $enclosure          = $data['enclosure'];
            $importFileType     = 'csv';


            $importFilePath = $processFilePath;
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
            $priceUpdater->log($resultData, 'shell');
            /************************\ END IMPORT \************************/

            $model                    = Mage::getModel('magepsycho_massimporterpro/massimporterpro');
            $totalCountRows           = $priceUpdater->getTotalCount();
            $successCountRows         = $priceUpdater->getSuccessCount();
            $errorCountRows           = $priceUpdater->getErrorCount();
            $skipCountRows            = $priceUpdater->getSkipCount();
            $data['created_at']       = now();
            $data['updated_at']       = now();
            $data['import_type']      = 'price_importer';
            $data['import_via']       = 'shell';
            $data['import_file_type'] = $importFileType;
            $data['import_file']      = '/' . $importFile;
            $data['log_data']         = serialize($resultData);
            $data['total_rows']       = $totalCountRows;
            $data['success_rows']     = $successCountRows;
            $data['error_rows']       = $errorCountRows;
            $data['skipped_rows']     = $skipCountRows;
            $data['import_duration']  = (float)number_format($importStopAt - $importStartAt, 6);
            $model->setData($data);
            try {
                $model->save();
                if ($reindexAfterImport) {
                    try {
                        echo "Reindexing Started...\n";
                        Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price')->reindexAll();
                        echo "Reindexing Done!\n";
                    } catch (Exception $e) {
                        $error = Mage::helper('magepsycho_massimporterpro')->__('There was an error while rebuilding Product prices index:<br />' . $e->getMessage());
                        echo "Import Error: " . $error . "\n";
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                echo "Import Error: " . $error . "\n";
            }
            echo "Price Import Completed!\n";
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
MagePsycho_Massimporterpro Price Importer script (V 1.1.0)
NAME
    priceimporter.php

SYNOPSIS
    php -f priceimporter.php -- [OPTIONS]
    Example: php -f priceimporter.php -- -import "path/to/csv/file.csv" -reindex

OPTIONS
    -help                       This help
    -import <path/to/csv>       CSV file path (relative or absolute path supported)
    -reindex                    Reindex price catalog

USAGE;
    }
}

$shell = new MagePsycho_Massimporterpro_Shell_Priceimporter();
$shell->run();
