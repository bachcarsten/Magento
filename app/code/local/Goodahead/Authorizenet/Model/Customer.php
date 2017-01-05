<?php

class Goodahead_Authorizenet_Model_Customer
    extends Mage_Core_Model_Abstract
{
    /**
     * Init customer
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('goodahead_authorizenet/customer');
    }

    /**
     * Get MerchantCustomerID
     *
     * @return string
     */
    public function getNewMerchantId()
    {
        //if (!$prefixId = $this->getPrefixId()) {
            $prefixId = Mage::helper('goodahead_authorizenet/config')->getPrefixId();
            $this->setPrefixId($prefixId);
        //}
        return $prefixId . $this->getCustomerId();
    }

    /**
     * Load by customer ID
     *
     * @param int $customerId
     * @return Goodahead_Authorizenet_Model_Customer
     */
    public function loadByCustomerId($customerId)
    {
        $collection = $this->getCollection();
        $collection
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('login_id', Mage::helper('goodahead_authorizenet/config')->getConfigData('login'));
        $model = $collection->getFirstItem();
        $this->setData($model->getData());
        $this->setOrigData(null, $model->getOrigData());

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        if (!$this->hasData('email')) {
            $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            $this->setData('email', $customer->getEmail());
        }
        return $this->getData('email');
    }

    /**
     * Add login ID
     *
     * @return void
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getLoginId()) {
            $this->setLoginId(Mage::helper('goodahead_authorizenet/config')->getConfigData('login'));
        }
    }
}