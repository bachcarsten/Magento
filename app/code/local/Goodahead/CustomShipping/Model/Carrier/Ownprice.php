<?php

class Goodahead_CustomShipping_Model_Carrier_Ownprice
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'goodahead_ownprice';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        # TODO: Add validation for site area. If NOT admin, than return false
        if ( Mage::app()->getStore()->getCode() != Mage_Core_Model_Store::ADMIN_CODE ) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingPrice = '0.00';

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }
}
