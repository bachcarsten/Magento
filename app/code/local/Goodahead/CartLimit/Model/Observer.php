<?php
class Goodahead_CartLimit_Model_Observer
{
    public function cartSaveAfter($observer)
    {
        $quote = $observer->getCart()->getQuote();
        $minLimit = Mage::getStoreConfig('cartlimit/limit/min_limit');
        $groupLimit = $this->_getSession()->getGoodaheadCartlimit();
        $isEnabled = Mage::getStoreConfig('cartlimit/limit/enabled');
        if ($isEnabled
            && ($groupLimit && $groupLimit > $quote->getItemsQty()
                || !$groupLimit && $minLimit > $quote->getItemsQty())
        ) {
            $quote->setDisableButton(true);
//            $quote->setHasError(true);
        }
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function checkoutActionPredispatch($observer)
    {
        /* @var $controller Mage_Checkout_OnepageController */
        $controller = $observer->getControllerAction();
        if (!$controller instanceof AW_Onestepcheckout_IndexController) {
            return;
        }

        $quote = $this->_getSession()->getQuote();
        $minLimit = Mage::getStoreConfig('cartlimit/limit/min_limit');
        $groupLimit = $this->_getSession()->getGoodaheadCartlimit();
        $isEnabled = Mage::getStoreConfig('cartlimit/limit/enabled');
        if ($quote->getId()
            && $isEnabled
            && $quote->getIsActive()
            && ($groupLimit && $groupLimit > $quote->getItemsQty()
                || !$groupLimit && $minLimit > $quote->getItemsQty())
        ) {
            $controller->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            $this->_getSession()
                ->addError(Mage::helper('goodahead_cartlimit')->__('Cannot process checkout because the total number product in cart less then %s', ($groupLimit ? $groupLimit : $minLimit)));
        }
    }

    /**
     * Adds cartlimit field to the group edit page
     */
    public function blockPrepareLayoutAfter($event)
    {
        $blockInstanse = $event->getBlock();
        $blockInstanseName = get_class($blockInstanse);

        switch ($blockInstanseName) {
            case 'Mage_Adminhtml_Block_Customer_Group_Edit_Form':
                $this->_getDecorator()->addShipingMethodsSelect($blockInstanse);
        }
    }

    /**
     * Loads the data and adds them to the registry
     */
    public function customerGroupLoadAfter($event)
    {
        $customerGroup = $event->getDataObject();
        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            $limit = Mage::getModel('goodahead_cartlimit/group')
                ->load($customerGroup->getId(), 'customer_group_id')
                ->getCartlimit();
            if( is_null(Mage::registry('goodahead_cartlimit')) ) {
                Mage::register('goodahead_cartlimit', $limit);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_CartLimit error: ' . $e->getMessage());
        }
    }

    /**
     * Loads the data sets them to the session
     */
    public function frontendCustomerGroupLoadAfter($event)
    {
        $customerGroup = $event->getDataObject();
        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            $limit = Mage::getModel('goodahead_cartlimit/group')->load($customerGroup->getId(), 'customer_group_id')
                ->getCartlimit();
            $this->_getSession()->setData('goodahead_cartlimit', $limit);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_CartLimit error: ' . $e->getMessage());
        }
    }

    /**
     * Saves the data when saving the group
     */
    public function customerGroupSaveAfter($event)
    {
        $request = Mage::app()->getRequest();
        $customerGroup = $event->getDataObject();

        if( $customerGroup->getId() === null ) {
            return $this;
        }

        try {
            Mage::getModel('goodahead_cartlimit/group')->load($customerGroup->getId(), 'customer_group_id')
            ->addData(array(
                'customer_group_id' => $customerGroup->getId(),
                'cartlimit'         => $request->getParam('goodahead_cartlimit')
            ))->save();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Goodahead_CartLimit error: ' . $e->getMessage());
        }
    }

    /**
     * @return Goodahead_CartLimit_Helper_Decorator_Customer_Group
     */
    protected function _getDecorator()
    {
        return Mage::helper('goodahead_cartlimit/decorator_customer_group');
    }

}