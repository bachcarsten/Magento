<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Rewrite/FrontCatalogInventoryObserver.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ PkfNdpqCQCtsQWkk('32a284cf48a7e7984425ff6e2aef0863'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryObserver extends Mage_CatalogInventory_Model_Observer
{
    // overide parent
    protected function _construct()
    {
        Mage::getModel('aitquantitymanager/moduleObserver')->onAitocModuleLoad();
        
        parent::_construct();
    }
    
    // override parent        
    public function saveInventoryData($observer)
    {
        $aWebsiteSaveIds = array();
        
        $product = $observer->getEvent()->getProduct();
        
        if (is_null($product->getStockData())) {
            if ($product->getIsChangedWebsites() || $product->dataHasChangedFor('status')) {
                
// start aitoc                
                $iWebsiteId = $product->getStore()->getWebsiteId();
                
                if (!$iWebsiteId)
                {
                    $iWebsiteId = 1;
                }
// finish aitoc                
                Mage::getSingleton('cataloginventory/stock_status')
                    ->updateStatus($product->getId(), null, $iWebsiteId); // aitoc code
///ait/                    ->updateStatus($product->getId());
            }
            return $this;
        }

        $item = $product->getStockItem();
        
        if (!$item) {
            $item = Mage::getModel('cataloginventory/stock_item');
        }
        $this->_prepareItemForSave($item, $product);
        
        $item->save();
        
        $aWebsiteSaveIds[] = $item->getWebsiteId();
        
        // start aitoc 
        
        $aDefaultData = $item->getProductDefaultItem($product->getId());
        
        $aProductWebsiteHash = $product->getWebsiteIds();
        
        if ($aProductWebsiteHash)
//        if ($aProductWebsiteHash AND $item->getUseDefaultWebsiteStock())
        {
            $aExistRecords = $item->getProductItemHash($product->getId());
            
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
                    
                    $oNewItem->setTypeId($item->getTypeId());
                    $oNewItem->setProductName($item->getProductName());
                    $oNewItem->setStoreId($item->getStoreId());
                    $oNewItem->setProductTypeId($item->getProductTypeId());
                    $oNewItem->setProductStatusChanged($item->getProductStatusChanged());

                    $oNewItem->save();
#                    d('sav');
#                    d($oNewItem->getdata());
                    
                    $aWebsiteSaveIds[] = $iWebsiteId;
                }
            }
        }
        
        if ($aWebsiteSaveIds AND !Mage::registry('aitquantitymanager_website_save_ids'))
        {
            Mage::register('aitquantitymanager_website_save_ids', $aWebsiteSaveIds);
        }
        
        // finish aitoc
        
        return $this;
    }

    // override parent        
    public function checkQuoteItemQty($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if (!$quoteItem || !$quoteItem->getProductId() || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }

        /**
         * Get Qty
         */
        $qty = $quoteItem->getQty();

        /**
         * Check item for options
         */
        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            $qty = $quoteItem->getProduct()->getTypeInstance(true)->prepareQuoteItemQty($qty, $quoteItem->getProduct());
            $quoteItem->setData('qty', $qty);

            foreach ($options as $option) {
                /* @var $option Mage_Sales_Model_Quote_Item_Option */
                $optionQty = $qty * $option->getValue();
                $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $option->getValue();

                $stockItem = $option->getProduct()->getStockItem();
                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                    Mage::throwException(Mage::helper('cataloginventory')->__('Stock item for Product in option is not valid'));
                }

                $qtyForCheck = $this->_getProductQtyForCheck($option->getProduct()->getId(), $increaseOptionQty);

                $result = $stockItem->checkQuoteItemQty($optionQty, $qtyForCheck, $option->getValue());

                if (!is_null($result->getItemIsQtyDecimal())) {
                    $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
                }

                if ($result->getHasQtyOptionUpdate()) {
                    $option->setHasQtyOptionUpdate(true);
                    $quoteItem->updateQtyOption($option, $result->getOrigQty());
                    $option->setValue($result->getOrigQty());
                    /**
                     * if option's qty was updates we also need to update quote item qty
                     */
                    $quoteItem->setData('qty', intval($qty));
                }
                if (!is_null($result->getMessage())) {
                    $option->setMessage($result->getMessage());
                }
                if (!is_null($result->getItemBackorders())) {
                    $option->setBackorders($result->getItemBackorders());
                }

                if ($result->getHasError()) {
                    $option->setHasError(true);
                    $quoteItem->setHasError(true)
                        ->setMessage($result->getQuoteMessage());
                    $quoteItem->getQuote()->setHasError(true)
                        ->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
                }
            }
        }
        else {
            $stockItem = $quoteItem->getProduct()->getStockItem();
            
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
//            if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {

            if (!$stockItem instanceof Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogInventoryStockItem) { // aitoc code
                Mage::throwException(Mage::helper('cataloginventory')->__('Stock item for Product is not valid'));
            }


            /**
             * When we work with subitem (as subproduct of bundle or configurable product)
             */
            if ($quoteItem->getParentItem()) {
                $rowQty = $quoteItem->getParentItem()->getQty()*$qty;
                /**
                 * we are using 0 because original qty was processed
                 */
                $qtyForCheck = $this->_getProductQtyForCheck($quoteItem->getProduct()->getId(), 0);
            }
            else {
                $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
                $rowQty = $qty;
                $qtyForCheck = $this->_getProductQtyForCheck($quoteItem->getProduct()->getId(), $increaseQty);
            }

            $result = $stockItem->checkQuoteItemQty($rowQty, $qtyForCheck, $qty);

            if (!is_null($result->getItemIsQtyDecimal())) {
                $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
                if ($quoteItem->getParentItem()) {
                    $quoteItem->getParentItem()->setIsQtyDecimal($result->getItemIsQtyDecimal());
                }
            }

            /**
             * Just base (parent) item qty can be changed
             * qty of child products are declared just during add process
             * exception for updating also managed by product type
             */
            if ($result->getHasQtyOptionUpdate()
                && (!$quoteItem->getParentItem()
                    || $quoteItem->getParentItem()->getProduct()->getTypeInstance(true)
                        ->getForceChildItemQtyChanges($quoteItem->getParentItem()->getProduct()))) {
                $quoteItem->setData('qty', $result->getOrigQty());
            }

            if (!is_null($result->getItemUseOldQty())) {
                $quoteItem->setUseOldQty($result->getItemUseOldQty());
            }
            if (!is_null($result->getMessage())) {
                $quoteItem->setMessage($result->getMessage());
                if ($quoteItem->getParentItem()) {
                    $quoteItem->getParentItem()->setMessage($result->getMessage());
                }
            }
            if (!is_null($result->getItemBackorders())) {
                $quoteItem->setBackorders($result->getItemBackorders());
            }

            if ($result->getHasError()) {
                $quoteItem->setHasError(true);
                $quoteItem->getQuote()->setHasError(true)
                    ->addMessage($result->getQuoteMessage(), $result->getQuoteMessageIndex());
            }
        }

        return $this;
    }

    // override parent        
    public function lockOrderInventoryData($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $productIds = array();

        /**
         * Do lock only for new order
         */
        if ($order->getId()) {
            return $this;
        }

        if ($order) {
            foreach ($order->getAllItems() as $item) {
                $productIds[] = $item->getProductId();
            }
        }
        if (!empty($productIds)) {
            Mage::getSingleton('cataloginventory/stock')->lockProductItems($productIds);
        }

// start aitoc code        
        if ($order->getStoreId() AND Mage::registry('aitoc_order_create_store_id') === NULL)
        {
            Mage::register('aitoc_order_create_store_id', $order->getStoreId()); // aitoc code
        }
        
// finish aitoc code        
        
        
        return $this;
    }

    // override parent        
    public function cancelOrderItem($observer)
    {
        $item = $observer->getEvent()->getItem();

        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

// start aitoc code        
        $oOrder = Mage::getModel('sales/order');
        
        $oOrder->load($item->getOrderId());
        
        if ($oOrder->getData() AND Mage::registry('aitoc_order_refund_store_id') === NULL)
        {
            Mage::register('aitoc_order_refund_store_id', $oOrder->getStoreId()); // aitoc code
        }
// finish aitoc code        
        
        if ($item->getId() && ($productId = $item->getProductId()) && empty($children) && $qty) {
            Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $qty);
        }
        return $this;
    }

    // override parent        
    public function refundOrderItem($observer)
    {
        $item = $observer->getEvent()->getCreditmemoItem();
        
// start aitoc code        
        if ($item->getStoreId() AND Mage::registry('aitoc_order_refund_store_id') === NULL)
        {
            Mage::register('aitoc_order_refund_store_id', $item->getStoreId()); // aitoc code
        }
// finish aitoc code        
        
        if ($item->getId() && $item->getBackToStock() && ($productId = $item->getProductId()) && ($qty = $item->getQty())) {
           Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $qty);
        }
        return $this;
    }

    // override parent        
    public function catalogProductWebsiteUpdate(Varien_Event_Observer $observer)
    {
        $websiteIds = $observer->getEvent()->getWebsiteIds();
        $productIds = $observer->getEvent()->getProductIds();

        foreach ($websiteIds as $websiteId) {
            foreach ($productIds as $productId) {
                
// start aitoc code
        
                $item = Mage::getModel('cataloginventory/stock_item');
                        
                $aDefaultData = $item->getProductDefaultItem($productId);
                
                $aProductWebsiteHash = $websiteIds;
                
                if ($aProductWebsiteHash)
                {
                    $aExistRecords = $item->getProductItemHash($productId);
                    
                    foreach ($aProductWebsiteHash as $iWebsiteId)
                    {
                        $sSaveMode = '';
                        
                        if (isset($aExistRecords[$iWebsiteId]))
                        {
                            
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
                            
                            $oNewItem->setTypeId($item->getTypeId());
                            $oNewItem->setProductName($item->getProductName());
                            $oNewItem->setStoreId($item->getStoreId());
                            $oNewItem->setProductTypeId($item->getProductTypeId());
                            $oNewItem->setProductStatusChanged($item->getProductStatusChanged());
                            $oNewItem->save();
                        }
                    }
                }
// finish aitoc code
                
                Mage::getSingleton('cataloginventory/stock_status')
                    ->updateStatus($productId, null, $websiteId);
            }
        }

        return $this;
    }

} } 