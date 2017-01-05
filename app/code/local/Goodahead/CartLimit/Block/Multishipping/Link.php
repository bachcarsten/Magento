<?php
class Goodahead_CartLimit_Block_Multishipping_Link
    extends Mage_Checkout_Block_Multishipping_Link
{
    public function isDisabled()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getDisableButton();
    }
}