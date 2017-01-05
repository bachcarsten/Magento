<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Mysql4/Product/Lowstock/Collection.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ rZRTWwipUpOsUkZZ('5815c171db344e5b9230f866f716d6ca'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitquantitymanager_Model_Mysql4_Product_Lowstock_Collection extends Mage_Reports_Model_Mysql4_Product_Lowstock_Collection
//class Mage_Reports_Model_Mysql4_Product_Lowstock_Collection extends Mage_Reports_Model_Mysql4_Product_Collection
{
	protected $_inventoryItemResource = null;
	protected $_inventoryItemJoined = false;
	protected $_inventoryItemTableAlias = 'lowstock_inventory_item';

        /**
         *         
         * @return Aitoc_Aitquantitymanager_Model_Mysql4_Product_Lowstock_Collection 
         */
        public function addToJoinFields($code, $field)
        {
            if (!isset($this->_joinFields[$code]))
            {
                $this->_joinFields[$code] = $field;   
            }

            return $this;
        }
	
	/**
	 * @return string
	 */
	protected function _getInventoryItemResource() 
	{
		if (is_null($this->_inventoryItemResource)) {
			$this->_inventoryItemResource = Mage::getResourceSingleton('cataloginventory/stock_item');
		}
		return $this->_inventoryItemResource;
    }

    /**
	 * @return string
	 */
	protected function _getInventoryItemTable() 
	{
		return $this->_getInventoryItemResource()->getMainTable();
    }

    /**
	 * @return string
	 */
	protected function _getInventoryItemIdField() 
	{
		return $this->_getInventoryItemResource()->getIdFieldName();
	}
	
	/**
	 * @return string
	 */
	protected function _getInventoryItemTableAlias() 
	{
		return $this->_inventoryItemTableAlias;
	}

	/**
	 * @param array|string $fields
	 * @return string
	 */
	protected function _processInventoryItemFields($fields) 
	{
		if (is_array($fields)) {
			$aliasArr = array();
			foreach ($fields as &$field) {
				if ( is_string($field) && strpos($field, '(') === false ) {
					$field = sprintf('%s.%s', $this->_getInventoryItemTableAlias(), $field);
				}   
			}
			unset($field);
			return $fields;
		}
		return sprintf('%s.%s', $this->_getInventoryItemTableAlias(), $fields);
	}
	
	/**
	 * Join cataloginventory_stock_item table for further
	 * stock_item values filters
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function joinInventoryItem($fields=array()) {
		if ( !$this->_inventoryItemJoined ) {
// start aitoc code
		    
            $iWebsiteId = 0;

            $controller = Mage::app()->getFrontController();
            
            if ($controller->getRequest()->getParam('website')) {
                $storeIds = Mage::app()->getWebsite($controller->getRequest()->getParam('website'))->getStoreIds();
                $iStoreId = array_pop($storeIds);
            } else if ($controller->getRequest()->getParam('group')) {
                $storeIds = Mage::app()->getGroup($controller->getRequest()->getParam('group'))->getStoreIds();
                $iStoreId = array_pop($storeIds);
            } else if ($controller->getRequest()->getParam('store')) {
                $iStoreId = (int)$controller->getRequest()->getParam('store');
            } else {
                $iStoreId = '';
            }

            if ($iStoreId)
            {
                $store = Mage::app()->getStore($iStoreId);
                $iWebsiteId = $store->getWebsiteId();
            }
            
		    if (!$iWebsiteId)
		    {
		        $iWebsiteId = Mage::helper('aitquantitymanager')->getHiddenWebsiteId();
		    }
// finish aitoc code
			$this->getSelect()->join(
                array($this->_getInventoryItemTableAlias() => $this->_getInventoryItemTable()),
				sprintf('e.%s=%s.product_id',
					$this->getEntity()->getEntityIdField(),
					$this->_getInventoryItemTableAlias()
//				),
				) . ' AND website_id = ' . $iWebsiteId, // fix for aitoc website
				array()
			);
			$this->_inventoryItemJoined = true;
		}
        if (is_string($fields)) {
            $fields = array($fields);
        }
        if (!empty($fields)) {
            $this->getSelect()->columns($this->_processInventoryItemFields($fields));
        }
		return $this;
	}
	
	/**
	 * @param array|string $typeFilter
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function filterByProductType($typeFilter)
	{
		if (!is_string($typeFilter) && !is_array($typeFilter)) {
			Mage::throwException(
				Mage::helper('catalog')->__('Wrong product type filter specified')
			);
		}
		$this->addAttributeToFilter('type_id', $typeFilter);
		return $this;
	}
	
	/**
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function filterByIsQtyProductTypes() 
	{
		$this->filterByProductType(
			array_keys(array_filter(Mage::helper('cataloginventory')->getIsQtyTypeIds()))
		);
		return $this;
	}
	
	/**
	 * @param int|null $storeId
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function useManageStockFilter($storeId=null)
	{
		$this->joinInventoryItem();
		$this->getSelect()->where(sprintf('IF(%s,%d,%s)=1', 
			$this->_processInventoryItemFields('use_config_manage_stock'), 
            (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK,$storeId), 
            $this->_processInventoryItemFields('manage_stock')));
        return $this;
	}
	
	/**
	 * @param int|null $storeId
	 * @return Mage_Reports_Model_Mysql4_Product_Collection
	 */
	public function useNotifyStockQtyFilter($storeId=null)
	{
#		$this->joinInventoryItem(array('qty'));
		$this->getSelect()->where(sprintf('qty < IF(%s,%d,%s)', 
			$this->_processInventoryItemFields('use_config_notify_stock_qty'), 
            (int) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY,$storeId), 
            $this->_processInventoryItemFields('notify_stock_qty')));
        return $this;
	}
} } 