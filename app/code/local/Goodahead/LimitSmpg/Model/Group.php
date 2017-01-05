<?php

class Goodahead_LimitSmpg_Model_Group extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_limitsmpg/group');
    }
    
    public function bulkSave($customerGroupId, $methodArray)
    {
        $this->getResource()->cleanupGroup($customerGroupId);
        
        if( is_array($methodArray) ) {
            foreach ($methodArray as $_method) {
                $this->getResource()->addRecord($customerGroupId, $_method);
            }
        }
        
        return $this;
    }
    
    public function loadArray($customerGroupId)
    {
        return $this->getResource()->loadArray($customerGroupId);
    }
}