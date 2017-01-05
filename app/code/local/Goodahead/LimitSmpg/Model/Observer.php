<?php

class Goodahead_LimitSmpg_Model_Observer
{
    public function blockPrepareLayoutAfter($event)
    {
        $blockInstanse = $event->getBlock();
        $blockInstanseName = get_class($blockInstanse);

        switch ($blockInstanseName) {
            case 'Mage_Adminhtml_Block_Customer_Group_Edit_Form':
                $this->_getDecorator()->addShipingMethodsSelect($blockInstanse);
        }
    }

    public function customerGroupSaveAfter($event)
    {
        $request = Mage::app()->getRequest();
        $customerGroup = $event->getDataObject();

        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            Mage::getModel('goodahead_limitsmpg/group')->bulkSave($customerGroup->getId(), $request->getParam('goodahead_limitsmpg'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_LimitSmpg error: ' . $e->getMessage());
        }
    }

    public function customerGroupLoadAfter($event)
    {
        $customerGroup = $event->getDataObject();
        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            $methodsArray = Mage::getModel('goodahead_limitsmpg/group')->loadArray($customerGroup->getId());
            if( is_null(Mage::registry('goodahead_limitsmpg')) ) {
                Mage::register('goodahead_limitsmpg', $methodsArray);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_LimitSmpg error: ' . $e->getMessage());
        }
    }

    public function frontendCustomerGroupLoadAfter($event)
    {
        $customerGroup = $event->getDataObject();
        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            $methodsArray = Mage::getModel('goodahead_limitsmpg/group')->loadArray($customerGroup->getId());
            $customerGroup->setData('goodahead_limit_smpg', $methodsArray);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_LimitSmpg error: ' . $e->getMessage());
        }
    }

    public function frontentSalesQuoteCollectTotalsAfter($observer)
    {
        $event = $observer->getEvent();
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $event->getQuote();
        $customerGroup = Mage::helper('goodahead_limitsmpg')->getQuoteCustomerGroup($quote);
        $onlyMethods = $customerGroup->getData('goodahead_limit_smpg');
        if (!empty($onlyMethods)) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                /* @var $address Mage_Sales_Model_Quote_Address */
                foreach ($address->getShippingRatesCollection() as $rate) {
                    if (!$rate->isDeleted()) {
                        if (array_search($rate->getCarrier(), $onlyMethods) === false) {
                            $rate->isDeleted(true);
                        }
                    }
                }
            }
        }
//        if ($result->isAvailable) {
//            $quote = $event->getQuote();
//            if (!empty($limitSmpg)) {
//                $onlyMethods = $customerGroup->getData('goodahead_limit_smpg');
//                if (array_search($event->getMethodInstance()->getCode(), $onlyMethods) === false) {
//                    $result->isAvailable = false;
//                }
//            }
//        }
    }

    public function frontentSalesQuoteAddressSaveBefore($observer)
    {
        $event = $observer->getEvent();
        /* @var $quoteAddress Mage_Sales_Model_Quote_Address */
        $quoteAddress = $event->getQuoteAddress();
        $customerGroup = Mage::helper('goodahead_limitsmpg')
            ->getQuoteCustomerGroup($quoteAddress->getQuote());
        $onlyMethods = $customerGroup->getData('goodahead_limit_smpg');
        if (!empty($onlyMethods)) {
            if ($quoteAddress->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
                foreach ($quoteAddress->getShippingRatesCollection() as $rate) {
                    if (!$rate->isDeleted()) {
                        if (array_search($rate->getCarrier(), $onlyMethods) === false) {
                            $rate->isDeleted(true);
                        }
                    }
                }
            }
        }
    }

        /**
     * @return Goodahead_LimitSmpg_Helper_Decorator_Customer_Group
     */
    protected function _getDecorator()
    {
        return Mage::helper('goodahead_limitsmpg/decorator_customer_group');
    }
}