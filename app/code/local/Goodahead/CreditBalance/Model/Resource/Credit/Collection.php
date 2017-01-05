<?php
class Goodahead_CreditBalance_Model_Resource_Credit_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('goodahead_creditbalance/credit');
    }
}