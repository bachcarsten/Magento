<?php

class Goodahead_Authorizenet_Model_Mysql4_Payment
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Init main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('goodahead_authorizenet/payment', 'id');
    }
}