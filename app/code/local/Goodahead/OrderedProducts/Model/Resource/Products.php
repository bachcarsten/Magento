<?php

class Goodahead_OrderedProducts_Model_Resource_Products extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_orderedproducts/products', 'id');
    }
    
    public function cleanupCustomer($customerId)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('customer_id = ?', $customerId);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
        
        return $this;
    }
    
    public function addRecord($groupId, $product_id, $position)
    {
        $this->_getWriteAdapter()->insert($this->getMainTable(), 
            array(
                'customer_id' => $groupId,
                'product_id'  => $product_id,
                'position'    => $position,
            )
        );
        
        return $this;
    }
    
    public function loadArray($customerId)
    {
        $select = $this->getReadConnection()->select();
        $select->from($this->getMainTable())
               ->where('customer_id = ?', $customerId);
               
        $array = $this->getReadConnection()->fetchAll($select);
        return $array;
    }
    
}