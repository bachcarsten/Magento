<?php
class Goodahead_CreditBalance_Model_Resource_Credit extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_creditbalance/credit', 'id');
    }
}