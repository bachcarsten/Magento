<?php
class Goodahead_General_Model_Resource_Category_Indexer_Product extends Mage_Index_Model_Resource_Abstract
{
    /**
     * Category table
     *
     * @var string
     */
    protected $_categoryTable;

    /**
     * Category product table
     *
     * @var string
     */
    protected $_categoryProductTable;

    /**
     * Product website table
     *
     * @var string
     */
    protected $_productWebsiteTable;

    /**
     * Store table
     *
     * @var string
     */
    protected $_storeTable;

    /**
     * Group table
     *
     * @var string
     */
    protected $_groupTable;

    /**
     * Array of info about stores
     *
     * @var array
     */
    protected $_storesInfo;

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('goodahead_general/category_product_idx', 'category_id');
        $this->_categoryTable        = $this->getTable('catalog/category');
        $this->_categoryProductTable = $this->getTable('catalog/category_product');
        $this->_productWebsiteTable  = $this->getTable('catalog/product_website');
        $this->_storeTable           = $this->getTable('core/store');
        $this->_groupTable           = $this->getTable('core/store_group');
    }

    /**
     * Process product save.
     * Method is responsible for index support
     * when product was saved and assigned categories was changed.
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */

    private function reindexProductById($prodId)
    {
        /**
         * Select relations to categories
         */
        $select = $this->_getWriteAdapter()->select()
            ->from(array('cp' => $this->_categoryProductTable), 'category_id')
            ->joinInner(array('ce' => $this->_categoryTable), 'ce.entity_id=cp.category_id', 'path')
            ->where('cp.product_id=:product_id');

        /**
         * Get information about product categories
         */
        $categories = $this->_getWriteAdapter()->fetchPairs($select, array('product_id' => $prodId));
        $allCategoryIds = array();

        foreach ($categories as $path) {
            $allCategoryIds = array_merge($allCategoryIds, explode('/', $path));
        }
        $allCategoryIds = array_unique($allCategoryIds);

        $idxAdapter = $this->_getWriteAdapter();
        $idxAdapter->beginTransaction();

        /**
         * Delete previous index data
         */
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array('product_id = ?' => $prodId)
        );

        foreach ($allCategoryIds as $catId) {
            $data = array(
                'category_id' => $catId,
                'product_id'  => $prodId
            );

            $idxAdapter->insert($this->getMainTable(), $data);
        }

        $idxAdapter->commit();
    }


    public function catalogProductSave(Mage_Index_Model_Event $event)
    {
        $productId = $event->getEntityPk();
        $data      = $event->getNewData();

        /**
         * Check if category ids were updated
         */
        if (!isset($data['category_ids'])) {
            return $this;
        }

        $this->reindexProductById($productId);

        return $this;
    }



    /**
     * Process Catalog Product mass action
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
     */
    public function catalogProductMassAction(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();

        /**
         * check is product ids were updated
         */
        if (!isset($data['product_ids'])) {
            return $this;
        }
        $productIds = $data['product_ids'];

        foreach ($productIds as $productId) {
            $this->reindexProductById($productId);
        }


        return $this;
    }



    /**
     * Rebuild all index data
     *
     * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
    */

    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->clearTemporaryIndexTable();
            $idxTable = $this->getIdxTable();
            $idxAdapter = $this->_getIndexAdapter();
            //$stores = $this->_getStoresInfo();

             /*//Build index for each store
            foreach ($stores as $storeData) {
                $storeId    = $storeData['store_id'];
                $websiteId  = $storeData['website_id'];
                $rootId     = $storeData['root_id'];

                //Prepare visibility for all enabled store products

                $enabledTable = $this->_prepareEnabledProductsVisibility($websiteId, $storeId);


                //Assign products not associated to any category to root category in index

                $select = $idxAdapter->select();
                $select->from(array('pv' => $enabledTable), array('category_id' => new Zend_Db_Expr($rootId), 'product_id'))
                    ->distinct(true)
                    ->joinLeft(array('cp' => $this->_categoryProductTable), 'pv.product_id = cp.product_id', array())
                    ->where('cp.product_id IS NULL');

                $query = $select->insertFromSelect(
                    $catProductTmp,
                    array('category_id', 'product_id'),
                    false
                );
                $idxAdapter->query($query);
            }*/


            $selectColumns = array(
                'category_id' => 'ce.entity_id',
                'product_id'  => 'cp.product_id'
            );

            $select = $idxAdapter->select();
            $select->from(array('cp' => $this->_categoryProductTable),  $selectColumns)
                ->distinct(true)
                ->join(array('ce2' => $this->_categoryTable), 'ce2.entity_id = cp.category_id', array())
                ->join(array('ce' => $this->_categoryTable), 'ce2.path LIKE CONCAT(ce.path, "%")', array());

            $query = $select->insertFromSelect(
                $idxTable,
                array('category_id', 'product_id'),
                false
            );
            $idxAdapter->query($query);


            /**
             * Clean up temporary tables
             */
            //$idxAdapter->delete($enabledTable);
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }


    /**
     * Create temporary table with enabled products visibility info
     *
     * @param int $websiteId
     * @param int $storeId
     * @return string temporary table name
     */
    protected function _prepareEnabledProductsVisibility($websiteId, $storeId)
    {
        $statusAttribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'status');
        $visibilityAttribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'visibility');
        $statusAttributeId = $statusAttribute->getId();
        $visibilityAttributeId = $visibilityAttribute->getId();
        $statusTable = $statusAttribute->getBackend()->getTable();
        $visibilityTable = $visibilityAttribute->getBackend()->getTable();

        /**
         * Prepare temporary table
         */
        $tmpTable = $this->_getEnabledProductsTemporaryTable();
        $this->_getIndexAdapter()->delete($tmpTable);

        $adapter        = $this->_getIndexAdapter();
        $visibilityExpr = $adapter->getCheckSql('pvs.value_id>0', $adapter->quoteIdentifier('pvs.value'),
            $adapter->quoteIdentifier('pvd.value'));
        $select         = $adapter->select()
            ->from(array('pw' => $this->_productWebsiteTable), array('product_id', 'visibility' => $visibilityExpr))
            ->joinLeft(
                array('pvd' => $visibilityTable),
                $adapter->quoteInto('pvd.entity_id=pw.product_id AND pvd.attribute_id=? AND pvd.store_id=0',
                    $visibilityAttributeId),
                array())
            ->joinLeft(
                array('pvs' => $visibilityTable),
                $adapter->quoteInto('pvs.entity_id=pw.product_id AND pvs.attribute_id=? AND ', $visibilityAttributeId)
                    . $adapter->quoteInto('pvs.store_id=?', $storeId),
                array())
            ->joinLeft(
                array('psd' => $statusTable),
                $adapter->quoteInto('psd.entity_id=pw.product_id AND psd.attribute_id=? AND psd.store_id=0',
                    $statusAttributeId),
                array())
            ->joinLeft(
                array('pss' => $statusTable),
                    $adapter->quoteInto('pss.entity_id=pw.product_id AND pss.attribute_id=? AND ', $statusAttributeId)
                        . $adapter->quoteInto('pss.store_id=?', $storeId),
                array())
            ->where('pw.website_id=?',$websiteId);
            /*->where($adapter->getCheckSql('pss.value_id > 0',
                $adapter->quoteIdentifier('pss.value'),
                $adapter->quoteIdentifier('psd.value')) . ' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);*/

        $query = $select->insertFromSelect($tmpTable, array('product_id' , 'visibility'), false);
        $adapter->query($query);
        return $tmpTable;
    }

    /**
     * Retrieve temporary table of category enabled products
     *
     * @return string
     */
    protected function _getEnabledProductsTemporaryTable()
    {
        if ($this->useIdxTable()) {
            return $this->getTable('catalog/category_product_enabled_indexer_idx');
        }
        return $this->getTable('catalog/category_product_enabled_indexer_tmp');
    }



    /**
     * Retrieve temporary decimal index table name
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        return $this->getTable('goodahead_general/category_product_idx');
    }
}
