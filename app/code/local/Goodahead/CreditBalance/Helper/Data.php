<?php
class Goodahead_CreditBalance_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Check whether notify checkbox was checked
     *
     * @return bool
     */
    public function canSendEmail()
    {
        if (Mage::app()->getFrontController()->getRequest()->getParam('credit_notify')) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve 'enabled' configuration node
     *
     * @return null|string
     */
    public function isEnabled()
    {
        return Mage::app()->getStore()->getConfig('creditbalance/credit/enabled');
    }
}