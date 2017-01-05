<?php
class Goodahead_CreditBalance_Block_Credit extends Mage_Core_Block_Template
{
    /** @var Goodahead_CreditBalance_Model_Credit|null  */
    protected $_credit = null;

    protected function _construct()
    {
        $this->_credit = Mage::getModel('goodahead_creditbalance/credit')
            ->loadByCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
    }

    public function getBalance()
    {
        return $this->_credit->getBalance();
    }

    public function isEnabled()
    {
        return $this->_credit->getEnabled();
    }
}