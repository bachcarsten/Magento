<?php

class Goodahead_Authorizenet_Block_Form_Cc
    extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('goodahead/authorizenet/form/cc.phtml');
    }

    /**
     * Get payment profiles
     *
     * @return bool|array
     */
    public function getPaymentProfiles()
    {
        if (!$this->_getCustomerId()) {
            return false;
        }

        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer');
        $authorizenetCustomer->loadByCustomerId($this->_getCustomerId());

        if (!$authorizenetCustomer->getId()) {
            return false;
        }

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorize
         */
        $authorize = Mage::getModel('goodahead_authorizenet/authorizenet');
        return $authorize->getPaymentProfiles($authorizenetCustomer);
    }

    /**
     * Get customer id
     *
     * @return null|int
     */
    protected function _getCustomerId()
    {
        return $this->getMethod()->getInfoInstance()->getQuote()->getCustomerId();
    }
}