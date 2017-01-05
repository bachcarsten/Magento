<?php

class Goodahead_Authorizenet_Block_Account_Card
    extends Mage_Core_Block_Template
{
    /**
     * Get new action
     *
     * @return string
     */
    public function getNewAction()
    {
        return $this->getUrl('goodahead_authorizenet/account/new');
    }

    /**
     * Get new action
     *
     * @return string
     */
    public function getEditAction()
    {
        return $this->getUrl('goodahead_authorizenet/account/edit');
    }

    /**
     * Get delete action
     *
     * @return string
     */
    public function getDeleteAction()
    {
        return $this->getUrl('goodahead_authorizenet/account/delete');
    }

    /**
     * Get payment profiles
     *
     * @return bool|array
     */
    public function getPaymentProfiles()
    {
        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer');
        $authorizenetCustomer->loadByCustomerId($this->_getSession()->getCustomer()->getId());

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
     * Get session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}