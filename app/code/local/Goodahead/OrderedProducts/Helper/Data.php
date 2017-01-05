<?php

class Goodahead_OrderedProducts_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getHistoryUrl()
    {
        return $this->_getUrl('customer/history');
    }
}