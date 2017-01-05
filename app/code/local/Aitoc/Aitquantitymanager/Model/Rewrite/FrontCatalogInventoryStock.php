<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Rewrite/FrontCatalogInventoryStock.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ jBcCrophhhkshDBB('781af016c807e723602a16753f2febe4'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStock extends Mage_CatalogInventory_Model_Stock
{
    // overide parent
    protected function _construct()
    {
        Mage::getModel('aitquantitymanager/moduleObserver')->onAitocModuleLoad();
        
        parent::_construct();
    }
    
    // overide parent
    public function addItemsToProducts($productCollection)
    {
// start aitoc code        
        $iWebsiteId = Mage::app()->getStore($productCollection->getStoreId())->getWebsiteId();
        
        if (!$iWebsiteId) 
        {
            $iWebsiteId = 1; // default
        }
// finish aitoc code        
        
        $items = $this->getItemCollection()
            ->addProductsFilter($productCollection)
            ->joinStockStatus($productCollection->getStoreId())
            ->addFieldToFilter('main_table.website_id', $iWebsiteId) // aitoc code
            ->load();
            
        if (!$items->getSize())
        {
            $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId(); // default
            
            $items = $this->getItemCollection()
                ->addProductsFilter($productCollection)
                ->joinStockStatus($productCollection->getStoreId())
->addFieldToFilter('main_table.website_id', $iWebsiteId) // aitoc
                ->load();
        }
            
        foreach ($items as $item) {
            foreach($productCollection as $product){
                if($product->getId()==$item->getProductId()){
                    if($product instanceof Mage_Catalog_Model_Product) {
                        $item->assignProduct($product);
                    }
                }
            }
        }
        
        return $this;
    }

    // override parent        
    public function getItemCollection()
    {
        return Mage::getModel('aitquantitymanager/mysql4_stock_item_collection')
            ->addStockFilter($this->getId());
    }

    // override parent        
    public function registerItemSale(Varien_Object $item)
    {
        if ($productId = $item->getProductId()) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if (Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
                if ($item->getStoreId()) {
                    $stockItem->setStoreId($item->getStoreId());
                }
                if ($stockItem->checkQty($item->getQtyOrdered()) || Mage::app()->getStore()->isAdmin()) {
                    $stockItem->subtractQty($item->getQtyOrdered());
                    
                    $stockItem->save();
// start ait                    
                    if ($stockItem->getUseDefaultWebsiteStock())
                    {
                        $this->setAfterSaveDefaultInventoryData($stockItem, $productId);
                    }
// fin ait                    
                }
            }
        }
        else {
            Mage::throwException(Mage::helper('cataloginventory')->__('Can not specify product identifier for order item'));
        }
        return $this;
    }    
     
    /**
     * override parent
     */
    public function registerProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);
        $item = Mage::getModel('cataloginventory/stock_item');
        $this->_getResource()->beginTransaction();
        $stockInfo = $this->_getResource()->getProductsStock($this, array_keys($qtys), true);
        $fullSaveItems = array();
        foreach ($stockInfo as $itemInfo) {
            $item->setData($itemInfo);
            if (!$item->checkQty($qtys[$item->getProductId()])) {
                $this->_getResource()->commit();
                Mage::throwException(Mage::helper('cataloginventory')->__('Not all products are available in the requested quantity'));
            }
            $item->subtractQty($qtys[$item->getProductId()]);
            if (!$item->verifyStock() || $item->verifyNotification()) {
                $fullSaveItems[] = clone $item;
            }
        }
        $this->_getResource()->correctItemsQty($this, $qtys, '-');
        $this->_getResource()->commit();
        
        // start ait                    
        if ($item->getUseDefaultWebsiteStock())
        {
            $this->setAfterSaveDefaultInventoryData($item, $item->getProductId());
        }
        // fin ait          
        
        return $fullSaveItems;
    }
// start aitoc code

    public function setAfterSaveDefaultInventoryData($oSavedItem, $productId)
    {
        if (!$oSavedItem OR !$productId) return false;
        
        $aNewData = $oSavedItem->getData();
        
        $aExistRecords = $oSavedItem->getProductItemHash($productId);
     
        foreach ($aExistRecords as $iWebsiteId => $aItemData)
        {
            $sSaveMode = '';
            
            if (
                ($iWebsiteId == Mage::helper('aitquantitymanager')->getHiddenWebsiteId()) 
                    OR 
                ($aItemData['item_id'] != $oSavedItem->getId() AND $aItemData['use_default_website_stock'])
               )
            {
                $sSaveMode = 'edit';
            }
                
            if ($sSaveMode)
            {
                $oNewItem = Mage::getModel('cataloginventory/stock_item');
            
                $oNewItem->addData($aNewData);
            
                $oNewItem->setSaveWebsiteId($iWebsiteId);
                
                if ($sSaveMode == 'edit')
                {
                    $oNewItem->setId($aExistRecords[$iWebsiteId]['item_id']);
                    
                }
                else 
                {
                    $oNewItem->setId(null);
                }
                
                $oNewItem->save();
            }
        }
        return true;
    }

// finish aitoc code
    
    // override parent        
    public function backItemQty($productId, $qty)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        
        if ($stockItem->getId() && Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
            $stockItem->addQty($qty);
            if ($stockItem->getCanBackInStock() && $stockItem->getQty() > $stockItem->getMinQty()) {
                $stockItem->setIsInStock(true)
                    ->setStockStatusChangedAutomaticallyFlag(true);
            }
            $stockItem->save();
            
// start aitoc code                    
            if ($stockItem->getUseDefaultWebsiteStock())
            {
                $this->setAfterSaveDefaultInventoryData($stockItem, $productId);
            }
// finish aitoc code                    
            
            
        }
        return $this;
    }    
    
    
} } 