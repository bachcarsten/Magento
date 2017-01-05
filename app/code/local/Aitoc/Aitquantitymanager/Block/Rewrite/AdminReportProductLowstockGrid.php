<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Block/Rewrite/AdminReportProductLowstockGrid.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ jBcCrophhhkshDBB('d296cbacdc4c7418260fdb78aa34c0fa'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitquantitymanager_Block_Rewrite_AdminReportProductLowstockGrid extends Mage_Adminhtml_Block_Report_Product_Lowstock_Grid
{
    // override parent
    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

//        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
        $collection = Mage::getModel('aitquantitymanager/mysql4_product_lowstock_collection') // aitoc code
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter($storeId)
            ->useNotifyStockQtyFilter($storeId)
            ->setOrder('qty', 'asc');

        // FIXED repeating products and lack of "qty" alias in Aitoc_Aitquantitymanager_Model_Mysql4_Product_Lowstock_Collection::_joinFields
        $collection->getSelect()->distinct();
        $collection->addToJoinFields('qty', array('table' => 'lowstock_inventory_item', 'field' => 'qty'));

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }
        
        // start aitoc code
        
        if ($storeId)
        {
            $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        }
        else 
        {
            $websiteId = 0;
        }
        
        if (!$websiteId) // show NO results 
        {
            $collection->getSelect()->where('lowstock_inventory_item.website_id = 0');
        }
        
        // finish aitoc code
                 
        $this->setCollection($collection);
#        return parent::_prepareCollection();
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection(); // aitoc code
    }
    

} } 