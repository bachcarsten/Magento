<?php

class Goodahead_LimitPmpg_Model_Observer
{
    public function blockPrepareLayoutAfter($event)
    {
        $blockInstanse = $event->getBlock();
        $blockInstanseName = get_class($blockInstanse);

        switch ($blockInstanseName) {
            case 'Mage_Adminhtml_Block_Customer_Group_Edit_Form':
                $this->_getDecorator()->addPaymentMethodsSelect($blockInstanse);
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
            Mage::getModel('goodahead_limitpmpg/group')->bulkSave($customerGroup->getId(), $request->getParam('goodahead_limitpmpg'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_LimitPmpg error: ' . $e->getMessage());
        }
    }

    public function customerGroupLoadAfter($event)
    {
        $customerGroup = $event->getDataObject();
        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            $methodsArray = Mage::getModel('goodahead_limitpmpg/group')->loadArray($customerGroup->getId());
            if( is_null(Mage::registry('goodahead_limitpmpg')) ) {
                Mage::register('goodahead_limitpmpg', $methodsArray);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_LimitPmpg error: ' . $e->getMessage());
        }
    }

    public function frontendCustomerGroupLoadAfter($event)
    {
        $customerGroup = $event->getDataObject();
        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            $methodsArray = Mage::getModel('goodahead_limitpmpg/group')->loadArray($customerGroup->getId());
            $customerGroup->setData('goodahead_limit_pmpg', $methodsArray);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_LimitPmpg error: ' . $e->getMessage());
        }
    }

    public function frontentPaymentMethodIsActive($observer)
    {
        $event = $observer->getEvent();
        $result = $event->getResult();
        if ($result->isAvailable) {
            $quote = $event->getQuote();
            if ($quote) {
                $customerGroup = Mage::helper('goodahead_limitpmpg')->getQuoteCustomerGroup($quote);
            } else {
                $customerGroup = Mage::getModel('customer/group')->load(Mage::getSingleton('customer/session')->getCustomerGroupId());
            }
            $limitPmpg = $customerGroup->getData('goodahead_limit_pmpg');
            if (!empty($limitPmpg)) {
                if (array_search($event->getMethodInstance()->getCode(), $limitPmpg) === false) {
                    $result->isAvailable = false;
                }
            }
        }
    }

    /**
     * @return Goodahead_LimitPmpg_Helper_Decorator_Customer_Group
     */
    protected function _getDecorator()
    {
        return Mage::helper('goodahead_limitpmpg/decorator_customer_group');
    }
}