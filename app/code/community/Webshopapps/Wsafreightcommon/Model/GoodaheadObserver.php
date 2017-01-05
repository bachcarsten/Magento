<?php

class Webshopapps_Wsafreightcommon_Model_GoodaheadObserver
{
    public function addressCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        /* @var $address Mage_Sales_Model_Quote_Address */
        $address = $observer->getEvent()->getQuoteAddress();
        $reCollect = Mage::app()->getRequest()->getPost('collect_shipping_rates')
            || ($address->getAddressType() == 'shipping' && $address->getCollectShippingRates());

        if ($address->getAddressType() == 'shipping' && $reCollect) {
            $address->collectShippingRates();

            $method = $address->getShippingMethod();

            $carrierCodes = array();
            $currentShippingRates = array();

            foreach($address->getAllShippingRates() as $rate) {
                /* @var $rate Mage_Sales_Model_Quote_Address_Rate */
                $carrierCodes[] = $rate->getCarrier();
                $currentShippingRates[$rate->getCode()] = $rate;
            }

            /* @var $shippingModel Mage_Shipping_Model_Shipping */
            $shippingModel = Mage::getModel('shipping/shipping');
            $shippingRatesResult = $shippingModel->collectRatesByAddress($address, $carrierCodes)->getResult();

            if ($shippingRatesResult) {
                $shippingRates = $shippingRatesResult->getAllRates();

                foreach ($shippingRates as $shippingRate) {
                    /* @var $shippingRate Mage_Shipping_Model_Rate_Result_Abstract */
                    $rateCode = $shippingRate->getCarrier() . '_' . $shippingRate->getMethod();
                    if (isset($currentShippingRates[$rateCode])) {
                        $currentShippingRates[$rateCode]->setFreightQuoteId($shippingRate->getFreightQuoteId());

                        if ($rateCode == $method) {
                            if ($shippingRate->getFreightQuoteId()) {
                                $address->setFreightQuoteId($shippingRate->getFreightQuoteId());
                            } else {
                                $address->setFreightQuoteId('');
                            }
                        }
                    }
                }
            }
        }
    }
}