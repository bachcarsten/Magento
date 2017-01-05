<?php

class Goodahead_PrivateSales_Model_System_Config_Backend_Active extends Mage_Core_Model_Config_Data
{
    protected function _afterSave()
    {
        if( $this->isValueChanged() ){
            Mage::app()->cleanCache(
                array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG)
            );
        }
    }
}