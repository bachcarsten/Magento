<?php

class Goodahead_Authorizenet_Helper_Config
    extends Mage_Core_Helper_Abstract
{
    /**
     * Is Enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->getConfigData('active');
    }

    /**
     * Get cc available types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        $availableTypes = $this->getConfigData('cctypes');
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        return $types;
    }

    /**
     * Get payment profiles
     *
     * @param int|Mage_Customer_Model_Customer $customerId
     * @return array|bool
     */
    public function getPaymentProfiles($customerId)
    {
        if ($customerId instanceof Mage_Customer_Model_Customer) {
            $customerId = $customerId->getId();
        }

        if (!$customerId) {
            return false;
        }

        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer');
        $authorizenetCustomer->loadByCustomerId($customerId);

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
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param mixed $store
     * @return mixed
     */
    public function getConfigData($field, $store = null)
    {
        $path = 'payment/goodahead_authorizenet/' . $field;
        return Mage::getStoreConfig($path, $store);
    }

    /**
     * Get prefix ID
     *
     * @param mixed $store
     * @return mixed
     */
    public function getPrefixId($store = null)
    {
        return $this->getConfigData('prefix_id', $store);
    }
}
