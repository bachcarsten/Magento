<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Adminhtml_PriceimporterController extends Mage_Adminhtml_Controller_Action
{
    protected function _title($text = null, $resetIfExists = true)
    {
        $helper = Mage::helper('magepsycho_massimporterpro');
        if ($helper->checkVersion('1.3.2.4', '<=')) {
            return $this;
        } else {
            return parent::_title($text, $resetIfExists);
        }
    }

    protected function _initAction()
    {
        $this->_title($this->__('Price Importer'))
            ->_title($this->__('Mass Importer Pro'));
        $this->loadLayout()
            ->_setActiveMenu('magepsycho_massimporterpro/priceimporter')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Price Importer'), Mage::helper('adminhtml')->__('Price Importer'));

        return $this;
    }

    public function indexAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('magepsycho_massimporterpro/massimporterpro')->load($id);

        //set default values
        $helper = Mage::helper('magepsycho_massimporterpro');
        $model->setData('delimiter', $helper->getConfig('delimiter', 'data_format'));
        $model->setData('enclosure', $helper->getConfig('enclosure', 'data_format'));
        $model->setData('tier_price_import_type', $helper->getConfig('tier_price_import_type', 'price_settings'));
        $model->setData('group_price_import_type', $helper->getConfig('group_price_import_type', 'price_settings'));
        $model->setData('reindex_after_import', $helper->getConfig('reindex_after_import', 'price_settings'));
        $model->setData('price_rounding', $helper->getConfig('price_rounding', 'price_settings'));
        $model->setData('rounding_nearest', $helper->getConfig('rounding_nearest', 'price_settings'));

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('priceimporter_data', $model);

            $this->_initAction();

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit'))
                ->_addLeft($this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magepsycho_massimporterpro')->__('Priceimporter does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function viewLogAction()
    {
        if ($logId = $this->getRequest()->getParam('id', 0)) {
            $helper  = Mage::helper('magepsycho_massimporterpro');
            $model   = Mage::getModel('magepsycho_massimporterpro/massimporterpro')->load($logId);
            $logData = unserialize($model->getLogData());
            $helper->displayFormattedArray($logData);
        } else {
            echo 'Log Id is incorrect!';
        }
    }

    public function uploadCsvAction()
    {
        $helper   = Mage::helper('magepsycho_massimporterpro');
        $isValid  = $helper->isValid();
        $isActive = $helper->isActive();
        if (!$isActive || ($isActive && !$isValid)) {
            Mage::getSingleton('adminhtml/session')->addNotice("Could complete your operation.");
            $this->_redirect('*/*/');
            return;
        }
        if ($data = $this->getRequest()->getPost()) {
            $helper = Mage::helper('magepsycho_massimporterpro');
            /********************************* CSV UPLOAD OPERATION *********************************/
            if (isset($_FILES['general']['name']['import_file_upload']) && $_FILES['general']['name']['import_file_upload'] != '') {
                try {
                    $fileName    = $_FILES['general']['name']['import_file_upload'];
                    $fileExt     = strtolower(substr(strrchr($fileName, "."), 1));
                    $fileNamewoe = rtrim($fileName, $fileExt);
                    $fileName    = $helper->slugify($fileNamewoe) . '.' . $fileExt;

                    $uploader = new Varien_File_Uploader('general[import_file_upload]');
                    $uploader->setAllowedExtensions(array('csv'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('var') . DS . 'magepsycho' . DS . 'massimporterpro' . DS . 'price_importer' . DS . 'web';
                    if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                    }
                    $uploader->save($path . DS, $fileName);
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magepsycho_massimporterpro')->__('CSV File: ' . $fileName . ' has been successfully uploaded.'));
                    $this->_redirect('*/*/');
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $this->_redirect('*/*/');
                    return;
                }
            }
            /********************************* CSV UPLOAD OPERATION *********************************/
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magepsycho_massimporterpro')->__('Unable to find CSV file for uploading.'));
        $this->_redirect('*/*');
    }

    public function checkCsvAction()
    {
        $helper   = Mage::helper('magepsycho_massimporterpro');
        $isValid  = $helper->isValid();
        $isActive = $helper->isActive();
        if (!$isActive || ($isActive && !$isValid)) {
            Mage::getSingleton('adminhtml/session')->addNotice("Could complete your operation.");
            $this->_redirect('*/*/');
            return;
        }
        if ($data = $this->getRequest()->getPost('general')) {

            /************************\ START IMPORT CHECK \************************/
            $helper = Mage::helper('magepsycho_massimporterpro');
            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            $delimiter          = ',';
            $enclosure          = '"';
            $importFile         = $data['import_file'];
            $importFileType     = 'csv';
            $baseImporterFile   = 'magepsycho' . DS . 'massimporterpro' . DS . 'price_importer' . DS . 'web' . DS . $importFile;
            $importFilePath     = Mage::getBaseDir('var') . DS . $baseImporterFile;

            $csv				= new Varien_File_Csv();
            $csv->setDelimiter($delimiter);
            $csv->setEnclosure($enclosure);
            $data				= $csv->getData($importFilePath);
            $fields				= array_shift($data);

            //@todo checks
            // check if field name exists, customer group exists

            $checkMessages = array();
            $checkMessages[] = 'Check Import Results:';
            $checkMessages[] = 'CSV File: ' . './var/' . $baseImporterFile;
            $checkMessages[] = 'CSV Headers: ' . Mage::helper('core/string')->truncate(implode(',', $fields), 1000);
            $checkMessages[] = 'Total Columns: ' . count($fields);
            $checkMessages[] = 'Total Rows: ' . count($data);
            $checkMessages[] = '...';
            $checkMessages[] = '(If CSV headers are more than expected, then there is something wrong with the CSV format. Try to format file properly using comma(,) as separator, double quote(") as enclosure, UTF-8 as encoding. We recommend OpenOffice Tool for CSV formatting.)';
            //var_dump($fields, $data); exit;

            /************************\ END IMPORT CHECK \************************/

            try {

                Mage::getSingleton('adminhtml/session')->addNotice(implode('<br />', $checkMessages));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magepsycho_massimporterpro')->__('Unable to find Importer to check.'));
        $this->_redirect('*/*/');
    }

    public function saveAction()
    {
        $helper   = Mage::helper('magepsycho_massimporterpro');
        $isValid  = $helper->isValid();
        $isActive = $helper->isActive();
        if (!$isActive || ($isActive && !$isValid)) {
            Mage::getSingleton('adminhtml/session')->addNotice("Could complete your operation.");
            $this->_redirect('*/*/');
            return;
        }
        if ($data = $this->getRequest()->getPost('general')) {

            /************************\ START IMPORT \************************/
            $helper = Mage::helper('magepsycho_massimporterpro');
            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            $reindexAfterImport = isset($data['reindex_after_import']) ? $data['reindex_after_import'] : 0;
            $delimiter          = ',';
            $enclosure          = '"';
            $importFile         = $data['import_file'];
            $importFileType     = 'csv';
            $importFilePath     = Mage::getBaseDir('var') . DS . 'magepsycho' . DS . 'massimporterpro' . DS . 'price_importer' . DS . 'web' . DS . $importFile;
            $fileOptions        = array(
                'source'    => $importFilePath,
                'delimiter' => $delimiter,
                'enclosure' => $enclosure,
            );
            $importOptions      = array(
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
            $priceUpdater->log($resultData, 'web');
            /************************\ END IMPORT \************************/

            $model                    = Mage::getModel('magepsycho_massimporterpro/massimporterpro');
            $totalCountRows           = $priceUpdater->getTotalCount();
            $successCountRows         = $priceUpdater->getSuccessCount();
            $errorCountRows           = $priceUpdater->getErrorCount();
            $skipCountRows            = $priceUpdater->getSkipCount();
            $data['created_at']       = now();
            $data['updated_at']       = now();
            $data['import_type']      = 'price_importer';
            $data['import_via']       = 'web';
            $data['import_file_type'] = $importFileType;
            $data['import_file']      = '/' . $importFile;
            $data['log_data']         = serialize($resultData);
            $data['total_rows']       = $totalCountRows;
            $data['success_rows']     = $successCountRows;
            $data['error_rows']       = $errorCountRows;
            $data['skipped_rows']     = $skipCountRows;
            $data['import_duration']  = (float)number_format($importStopAt - $importStartAt, 6);

            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();

                $reindexMessage = '';
                if ($reindexAfterImport) {
                    try {
                        Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price')->reindexAll();
                        $reindexMessage = '<br />...<br />Product Prices index was rebuilt successfully.';
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magepsycho_massimporterpro')->__('There was an error while rebuilding Product prices index:<br />' . $e->getMessage()));
                    }

                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magepsycho_massimporterpro')->__('Total %d no of rows were processed.<br /> Successfully updated rows#: %d <br />Skipped rows#: %d <br />Error occurred rows#: %d' . $reindexMessage . '<br />...<br />(Click \'Import History\' tab for more details.)', $totalCountRows, $successCountRows, $skipCountRows, $errorCountRows));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magepsycho_massimporterpro')->__('Unable to find Importer to save.'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('magepsycho_massimporterpro/priceimporter');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The Log has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function historyAction()
    {
        $this->getLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_history')->toHtml()
        );
    }

    public function massDeleteAction()
    {
        $priceimporterIds = $this->getRequest()->getParam('priceimporter');
        if (!is_array($priceimporterIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($priceimporterIds as $priceimporterId) {
                    $priceimporter = Mage::getModel('magepsycho_massimporterpro/massimporterpro')->load($priceimporterId);
                    $priceimporter->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were deleted.', count($priceimporterIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName = 'priceimporter.csv';
        $content  = $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_history')->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'priceimporter.xml';
        $content  = $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_history')->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportExcelAction()
    {
        $fileName = 'priceimporter_excel.xml';
        $content  = $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_history')->getExcel($fileName);
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('magepsycho_massimporterpro/priceimporter');
    }
}