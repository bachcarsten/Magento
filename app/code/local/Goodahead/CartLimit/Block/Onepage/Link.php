<?php
class Goodahead_CartLimit_Block_Onepage_Link
    extends Mage_Checkout_Block_Onepage_Link
{
    public function isDisabled()
    {
        $validateMinimumAmount = !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
        $disableButton         = Mage::getSingleton('checkout/session')->getQuote()->getDisableButton();

        if ($validateMinimumAmount  || $disableButton) {
            return true;
        }

        return false;
    }
}