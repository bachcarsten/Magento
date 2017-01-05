<?php

class Goodahead_Authorizenet_Block_Adminhtml_Customer_Info_Form
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'goodahead/authorizenet/customer/info/form.phtml';

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