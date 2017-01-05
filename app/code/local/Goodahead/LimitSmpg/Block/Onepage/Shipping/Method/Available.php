<?php
class Goodahead_LimitSmpg_Block_Onepage_Shipping_Method_Available
    extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getShippingRates()
    {

        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();
            $quote = Mage::getSingleton('checkout/session')->getQuote();

            $customerGroup = Mage::helper('goodahead_limitsmpg')->getQuoteCustomerGroup($quote);
            $onlyMethods = $customerGroup->getData('goodahead_limit_smpg');

            if (!empty($onlyMethods)) {
                $result = array();
                foreach ($groups as $keyGroup => $value) {
                    foreach ($value as $_rate) {
                        if (!$_rate->isDeleted()) {
                            if (array_search($_rate->getCarrier(), $onlyMethods) !== false) {
                                $result[$keyGroup][] = $_rate;
                            }
                        }
                    }
                }
                $this->_rates = $result;
            } else {
                $this->_rates = $groups;
            }

//            if (!empty($onlyMethods)) {
//                foreach ($address->getShippingRatesCollection() as $rate) {
//                        if (!$rate->isDeleted()) {
//                            if (array_search($rate->getCarrier(), $onlyMethods) === false) {
//                                $rate->isDeleted(true);
//                            }
//                        }
//                    }
//                }
//            }

            return $this->_rates;
        }

        return $this->_rates;
    }
}