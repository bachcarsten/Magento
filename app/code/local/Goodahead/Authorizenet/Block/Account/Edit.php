<?php

class Goodahead_Authorizenet_Block_Account_Edit
    extends Mage_Core_Block_Template
{
    /**
     * Get form action
     *
     * @return string
     */
    public function getUpdateAction()
    {
        return $this->getUrl('goodahead_authorizenet/account/update');
    }

    /**
     * Has verification
     *
     * @return bool
     */
    public function hasVerification()
    {
        $useCcv = $this->_getHelper()->getConfigData('useccv');
        if (is_null($useCcv)) {
            return true;
        }
        return (bool) $useCcv;
    }

    /**
     * Get payment profile id
     *
     * @return int
     */
    public function getPaymentProfileId()
    {
        $paymentProfileId = (int) $this->getRequest()->getParam('id');
        return $paymentProfileId;
    }

    /**
     * Get payment profile
     *
     * @return bool|array
     */
    public function getPaymentProfile()
    {
        $paymentProfileId = $this->getPaymentProfileId();
        $paymentProfile = Mage::getModel('goodahead_authorizenet/payment')->loadByProfileId($paymentProfileId);
        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer');
        $authorizenetCustomer->loadByCustomerId($this->_getSession()->getCustomer()->getId());

        if (!$authorizenetCustomer->getId()) {
            return false;
        }

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorize
         */
        $authorize = Mage::getModel('goodahead_authorizenet/authorizenet');
        return $authorize->getPaymentProfile($authorizenetCustomer, $paymentProfile);
    }

    /**
     * Get session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get helper
     *
     * @return Goodahead_Authorizenet_Helper_Config
     */
    protected function _getHelper()
    {
        return Mage::helper('goodahead_authorizenet/config');
    }
}