<?php

class Goodahead_CartLimit_Model_Mysql4_Group extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_cartlimit/group', 'id');
    }
    
    public function addRecord($groupId, $limit)
    {
        $this->_getWriteAdapter()->insert($this->getMainTable(), 
            array(
                'customer_group_id' => $groupId,
                'cartlimit' => $limit,
            )
        );
        
        return $this;
    }
}