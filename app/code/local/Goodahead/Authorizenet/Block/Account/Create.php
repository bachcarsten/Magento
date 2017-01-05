<?php

class Goodahead_Authorizenet_Block_Account_Create
    extends Mage_Core_Block_Template
{
    /**
     * Get form action
     *
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('goodahead_authorizenet/account/save');
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
     * Get cc available types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        $availableTypes = $this->_getHelper()->getConfigData('cctypes');
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
     * Get helper
     *
     * @return Goodahead_Authorizenet_Helper_Config
     */
    protected function _getHelper()
    {
        return Mage::helper('goodahead_authorizenet/config');
    }
}