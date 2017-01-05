<?php

class Goodahead_OrderedProducts_Model_Products extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_orderedproducts/products');
    }
    
    public function bulkSave($customerId, $data)
    {
        $this->getResource()->cleanupCustomer($customerId);
        
        if (is_array($data) ) {
            foreach ($data as $recordData) {
                $this->getResource()->addRecord($customerId, $recordData['product_id'], $recordData['position']);
            }
        }
        
        return $this;
    }
    
    public function loadArray($customerId)
    {
        return $this->getResource()->loadArray($customerId);
    }
}