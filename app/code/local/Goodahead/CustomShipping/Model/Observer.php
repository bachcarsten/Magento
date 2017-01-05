<?php

class Goodahead_CustomShipping_Model_Observer
{
    public function addressCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        /* @var $address Mage_Sales_Model_Quote_Address */
        $address = $observer->getEvent()->getQuoteAddress();

        $orderPostData = Mage::app()->getRequest()->getPost('order');

        $reCollect = Mage::app()->getRequest()->getPost('collect_shipping_rates')
            || ($address->getAddressType() == 'shipping' && $address->getCollectShippingRates())
            || (is_array($orderPostData) && isset($orderPostData['shipping_method_price']));

        if ($address->getAddressType() == 'shipping' && $reCollect) {
            $method = $address->getShippingMethod();

            $rateCode = 'goodahead_ownprice_goodahead_ownprice';

            if ($method == $rateCode && is_array($orderPostData) && isset($orderPostData['shipping_method_price'])) {
                $address->collectShippingRates();

                /* @var $rate Mage_Sales_Model_Quote_Address_Rate */
                $rate = $address->getShippingRateByCode($rateCode);

                if ($rate) {
                    $price = floatval($orderPostData['shipping_method_price']);
                    $rate->setPrice($price);
                    if (!empty($orderPostData['shipping_method_carrier_title'])) {
                        $rate->setCarrierTitle($orderPostData['shipping_method_carrier_title']);
                    }
                    if (!empty($orderPostData['shipping_method_method_title'])) {
                        $rate->setMethodTitle($orderPostData['shipping_method_method_title']);
                    }
                }
            }
       }
    }
}