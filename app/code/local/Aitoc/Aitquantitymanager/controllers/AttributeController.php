<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/controllers/AttributeController.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ ErNQVophRhdsRPrr('fb2359577149a7d3b70be97c16060769'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
require_once 'Mage/Adminhtml/controllers/Catalog/Product/Action/AttributeController.php';

class Aitoc_Aitquantitymanager_AttributeController extends Mage_Adminhtml_Catalog_Product_Action_AttributeController
#class Mage_Adminhtml_Catalog_Product_Action_AttributeController extends Mage_Adminhtml_Controller_Action
{

    public function saveAction()
    {
        if (!$this->_validateProducts()) {
            return;
        }

        /* Collect Data */
        $inventoryData      = $this->getRequest()->getParam('inventory', array());
        $attributesData     = $this->getRequest()->getParam('attributes', array());
        $websiteRemoveData  = $this->getRequest()->getParam('remove_website_ids', array());
        $websiteAddData     = $this->getRequest()->getParam('add_website_ids', array());

        /* Prepare inventory data item options (use config settings) */
        foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        try {
            if ($attributesData) {
                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $storeId    = $this->_getHelper()->getSelectedStoreId();

                foreach ($attributesData as $attributeCode => $value) {
                    $attribute = Mage::getSingleton('eav/config')
                        ->getAttribute('catalog_product', $attributeCode);
                    if (!$attribute->getAttributeId()) {
                        unset($attributesData[$attributeCode]);
                        continue;
                    }
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput    = new Zend_Filter_LocalizedToNormalized(array(
                                'date_format' => $dateFormat
                            ));
                            $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                                'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
                            ));
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    }
                }

                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($this->_getHelper()->getProductIds(), $attributesData, $storeId);
            }
            
            if ($inventoryData) {
                $stockItem = Mage::getModel('cataloginventory/stock_item');
#d(get_class($stockItem), 1);
                foreach ($this->_getHelper()->getProductIds() as $productId) {
                    $stockItem->setData(array());
                    $stockItem->loadByProduct($productId)
                        ->setProductId($productId);

                    $stockDataChanged = false;
                    foreach ($inventoryData as $k => $v) {
                        $stockItem->setDataUsingMethod($k, $v);
                        if ($stockItem->dataHasChangedFor($k)) {
                            $stockDataChanged = true;
                        }
                    }
                    
                    
// start aitoc code
                    $iStoreId = 0;
                    $iWebsiteId = 0;
                                
                    if ($controller = Mage::app()->getFrontController()) 
                    {
                        $oRequest = $controller->getRequest();
                        if ($oRequest->getParam('store')) {
                            $iStoreId = (int)$oRequest->getParam('store');
                        }
                    }
                            
                    if ($iStoreId) 
                    {
                        $store = Mage::app()->getStore($iStoreId);
                        $iWebsiteId = $store->getWebsiteId();
                    }
                    
                    $oItem = Mage::getResourceModel('cataloginventory/stock_item');
                    
                    if ($iWebsiteId)
                    {
                        $aItemData = $oItem->getDataByProductId($productId, $iWebsiteId);
                    }
                    else 
                    {
                        $aItemData = 1;
                    }
                    
// finish aitoc code                       
#                    d($stockItem->getdata());
                    
//                    if ($stockDataChanged) {
                    if ($stockDataChanged AND $aItemData) { // aitoc code
                        
                        $stockItem->save();
                        
                        $product = Mage::getModel('catalog/product')->load($productId);
                        
                        // start aitoc 
                        
                        $aDefaultData = $stockItem->getProductDefaultItem($product->getId());
                       
                        $aProductWebsiteHash = $product->getWebsiteIds();
                        
                        if ($aProductWebsiteHash)
                //        if ($aProductWebsiteHash AND $item->getUseDefaultWebsiteStock())
                        {
                            $aExistRecords = $stockItem->getProductItemHash($product->getId());
                            
                            foreach ($aProductWebsiteHash as $iWebsiteId)
                            {
                                $sSaveMode = '';
                                
                                if (isset($aExistRecords[$iWebsiteId]))
                                {
                                    if ($aExistRecords[$iWebsiteId]['use_default_website_stock'])
                                    {
                                        $sSaveMode = 'edit';
                                    }
                                }
                                else 
                                {
                                    if ($iWebsiteId != Mage::helper('aitquantitymanager')->getHiddenWebsiteId())
                                    {    
                                        $sSaveMode = 'add';
                                    }
                                }
                                                
                                if ($sSaveMode)
                                {
                                    $oNewItem = Mage::getModel('cataloginventory/stock_item');
                                
                                    $oNewItem->addData($aDefaultData);
                                
                                    $oNewItem->setSaveWebsiteId($iWebsiteId);
                                    
                                    if ($sSaveMode == 'edit')
                                    {
                                        $oNewItem->setId($aExistRecords[$iWebsiteId]['item_id']);
                                        
                                    }
                                    else 
                                    {
                                        $oNewItem->setId(null);
                                    }
                                    
                                    $oNewItem->setUseDefaultWebsiteStock(1);
                                    
                                    $oNewItem->setTypeId($stockItem->getTypeId());
                                    $oNewItem->setProductName($stockItem->getProductName());
                                    $oNewItem->setStoreId($stockItem->getStoreId());
                                    $oNewItem->setProductTypeId($stockItem->getProductTypeId());
                                    $oNewItem->setProductStatusChanged($stockItem->getProductStatusChanged());
                
                                    $aWebsiteSaveIds = array($iWebsiteId);
                /*
                                    if ($aWebsiteSaveIds AND !Mage::registry('aitquantitymanager_website_save_ids'))
                                    {
                                        Mage::register('aitquantitymanager_website_save_ids', $aWebsiteSaveIds);
                                    }
                */
                                    $oNewItem->save();
                                }
                            }
                        }
                    }
                }
            }
            if ($websiteAddData || $websiteRemoveData) {
                /* @var $actionModel Mage_Catalog_Model_Product_Action */
                $actionModel = Mage::getSingleton('catalog/product_action');
                $productIds  = $this->_getHelper()->getProductIds();

                if ($websiteRemoveData) {
                    $actionModel->updateWebsites($productIds, $websiteRemoveData, 'remove');
                }
                if ($websiteAddData) {
                    $actionModel->updateWebsites($productIds, $websiteAddData, 'add');
                }

                /**
                 * @deprecated since 1.3.2.2
                 */
                Mage::dispatchEvent('catalog_product_to_website_change', array(
                    'products' => $productIds
                ));

                $this->_getSession()->addNotice(
                    $this->__('Please refresh "Catalog Url Rewrites" and "Product Attributes" in System -> <a href="%s">Index Management</a>', $this->getUrl('adminhtml/process/list'))
                );
            }

            // fix for empty items for new websites
            
            $sItemTable = Mage::getSingleton('core/resource')->getTableName('aitquantitymanager/stock_item');
            $sProductTable = Mage::getSingleton('core/resource')->getTableName('catalog/product');
            $sWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
#d($this->_getHelper()->getProductIds());
            
            $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');            

            $select  = $oDb->select()
                ->from(array('e' => $sProductTable), array('entity_id'));
            $select->joinLeft(
                array('pw' => $sWebsiteTable),
                "pw.product_id = e.entity_id",
                array('pw.website_id')
            )
            ->joinLeft(
                array('item' => $sItemTable),
                'item.product_id = e.entity_id AND pw.website_id = item.website_id', // aitoc code
                array())
            ->where('item.item_id IS null')
            ->where('e.entity_id IN (' . implode(',', $this->_getHelper()->getProductIds()) . ')');

            $aMissingItems = $oDb->fetchAll($select->__toString());

            if ($aMissingItems)
            {
                $stockItem = Mage::getModel('cataloginventory/stock_item');  
                
                foreach ($aMissingItems as $aItem)
                {
                    $product = Mage::getModel('catalog/product')->load($aItem['entity_id']);
                    
                    $aDefaultData = $stockItem->getProductDefaultItem($aItem['entity_id']);

                    $oNewItem = Mage::getModel('cataloginventory/stock_item');
                    $oNewItem->addData($aDefaultData);
                
                    $oNewItem->setSaveWebsiteId($aItem['website_id']);
                    
                    $oNewItem->setId(null);
                    
                    $oNewItem->setUseDefaultWebsiteStock(1);
                    $oNewItem->setTypeId($product->getTypeId());
                    
#                    $oNewItem->setProductName($stockItem->getProductName());
#                    $oNewItem->setStoreId($stockItem->getStoreId());
#                    $oNewItem->setProductTypeId($stockItem->getProductTypeId());
#                    $oNewItem->setProductStatusChanged($stockItem->getProductStatusChanged());

#                    $aWebsiteSaveIds = array($iWebsiteId);
/*
                    if ($aWebsiteSaveIds AND !Mage::registry('aitquantitymanager_website_save_ids'))
                    {
                        Mage::register('aitquantitymanager_website_save_ids', $aWebsiteSaveIds);
                    }
*/
                    $oNewItem->save();
                }
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully updated',
                count($this->_getHelper()->getProductIds()))
            );
        
        }
        
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('There was an error while updating product(s) attributes'));
        }

        $this->_redirect('*/catalog_product/', array('store'=>$this->_getHelper()->getSelectedStoreId()));
    }
} } 