<?php

class Goodahead_Authorizenet_Model_Mysql4_Payment_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Init model and resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('goodahead_authorizenet/payment');
    }
}
