<?php
class Goodahead_CartLimit_Block_Message
    extends Mage_Core_Block_Template
{
    public function canShow()
    {
        return (bool)Mage::getSingleton('checkout/session')->getQuote()->getDisableButton();
    }

    public function getNeedCount()
    {
        return Mage::getStoreConfig('cartlimit/limit/min_limit');
    }
}