<?php

class Goodahead_LimitPmpg_Model_Mysql4_Group extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_limitpmpg/group', 'id');
    }
    
    public function cleanupGroup($groupId)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('customer_group_id = ?', $groupId);
        $this->_getWriteAdapter()->delete($this->getMainTable(), $condition);
        
        return $this;
    }
    
    public function addRecord($groupId, $method)
    {
        $this->_getWriteAdapter()->insert($this->getMainTable(), 
            array(
                'customer_group_id' => $groupId,
                'method_code' => $method,
            )
        );
        
        return $this;
    }
    
    public function loadArray($groupId)
    {
        $select = $this->getReadConnection()->select();
        $select->from($this->getMainTable(), 'method_code')
               ->where('customer_group_id = ?', $groupId);
               
        $array = $this->getReadConnection()->fetchCol($select);
        return $array;
    }
    
}