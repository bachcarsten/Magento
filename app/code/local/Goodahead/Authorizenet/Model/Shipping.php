<?php

class Goodahead_Authorizenet_Model_Shipping
    extends Mage_Core_Model_Abstract
{
    /**
     * Init customer
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('goodahead_authorizenet/shipping');
    }
}