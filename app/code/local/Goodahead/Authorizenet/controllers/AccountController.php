<?php

class Goodahead_Authorizenet_AccountController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Card lists
     *
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * New card form
     *
     * @return void
     */
    public function newAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Edit card form
     *
     * @return void
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Save card
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account');
        }

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorizenet
         */
        $authorizenet = Mage::getModel('goodahead_authorizenet/authorizenet');
        try {
            $response = $authorizenet->testAuthentication();
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        if (strtolower($response['code']) != 'ok') {
            Mage::getSingleton('customer/session')->addError($this->__('Cannot connect to authorize.net'));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        $fields = $this->getRequest()->getPost('profile');
        $object = new Varien_Object();
        $object->setData($fields);
        try {
            Mage::getModel('goodahead_authorizenet/payment')->validate($object);
            $fields['card_number']     = $object->getCcNumber();
            $fields['expiration_date'] = $object->getCcExpYear()  . '-'
                . (strlen($object->getCcExpMonth()) < 2 ? '0' . $object->getCcExpMonth() : $object->getCcExpMonth());
            $fields['card_code']       = $object->getCcCid();
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        $customer = $this->_getSession()->getCustomer();
        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer')->loadByCustomerId($customer->getId());
        if (!$authorizenetCustomer->getCustomerId()) {
            $authorizenetCustomer->setCustomerId($customer->getId());
            $authorizenetCustomer->save();
        }

        try {
            if ($authorizenetCustomer->getProfileId()) {
                $response = $authorizenet->createPaymentProfile($authorizenetCustomer, $fields);
            } else {
                $response = $authorizenet->createCustomerAndPaymentProfiles($authorizenetCustomer, $fields);
            }
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
        }

        if (strtolower($response['code']) != 'ok') {
            if (is_array($response['messages'])) {
                foreach ($response['messages'] as $message) {
                    Mage::getSingleton('customer/session')->addError($this->__($message->text));
                }
            } else {
                Mage::getSingleton('customer/session')->addError($this->__($response['messages']->text));
            }

            return $this->_redirect('goodahead_authorizenet/account');
        }

        Mage::getSingleton('customer/session')->addSuccess($this->__('Your card has been saved successfully.'));
        return $this->_redirect('goodahead_authorizenet/account');
    }

    /**
     * Update card
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function updateAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account');
        }

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorizenet
         */
        $authorizenet = Mage::getModel('goodahead_authorizenet/authorizenet');
        try {
            $response = $authorizenet->testAuthentication();
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        if (strtolower($response['code']) != 'ok') {
            Mage::getSingleton('customer/session')->addError($this->__('Cannot connect to authorize.net'));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        $fields = $this->getRequest()->getPost('profile');
        $object = new Varien_Object();
        $object->setData($fields);
        try {
            //Mage::getModel('goodahead_authorizenet/payment')->validate($object);
            //$fields['card_number']     = $object->getCcNumber();
            $fields['expiration_date'] = $object->getCcExpYear()  . '-'
                . (strlen($object->getCcExpMonth()) < 2 ? '0' . $object->getCcExpMonth() : $object->getCcExpMonth());
            $fields['card_code']       = $object->getCcCid();
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        $customer = $this->_getSession()->getCustomer();
        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer')->loadByCustomerId($customer->getId());
        if (!$authorizenetCustomer->getCustomerId()) {
            $authorizenetCustomer->setCustomerId($customer->getId());
            $authorizenetCustomer->save();
        }

        $paymentProfileId = $object->getPaymentProfileId();
        $paymentProfile = Mage::getModel('goodahead_authorizenet/payment')->loadByProfileId($paymentProfileId);
        try {
            if ($authorizenetCustomer->getProfileId() && $paymentProfile->getProfileId()) {
                $response = $authorizenet->updatePaymentProfile($authorizenetCustomer, $paymentProfile, $fields);
            } 
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__($e->getMessage()));
        }

        if (strtolower($response['code']) != 'ok') {
            if (is_array($response['messages'])) {
                foreach ($response['messages'] as $message) {
                    Mage::getSingleton('customer/session')->addError($this->__($message->text));
                }
            } else {
                Mage::getSingleton('customer/session')->addError($this->__($response['messages']->text));
            }

            return $this->_redirect('goodahead_authorizenet/account');
        }

        Mage::getSingleton('customer/session')->addSuccess($this->__('Your card has been updated successfully.'));
        return $this->_redirect('goodahead_authorizenet/account');
    }

    /**
     * Delete card
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        $payment = Mage::getModel('goodahead_authorizenet/payment')->load($id, 'profile_id');
        if (!$payment->getId()) {
            Mage::getSingleton('customer/session')->addNotice($this->__('Cannot find card.'));
            return $this->_redirect('goodahead_authorizenet/account');
        }

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorize
         */
        $authorize = Mage::getModel('goodahead_authorizenet/authorizenet');
        $result = $authorize->deletePaymentProfile($payment);
        if ($result === true) {
            Mage::getSingleton('customer/session')->addSuccess($this->__('Your card has been deleted successful.'));
        } else {
            Mage::getSingleton('customer/session')->addError($this->__('Cannot delete card.'));
        }

        return $this->_redirect('goodahead_authorizenet/account');
    }

    /**
     * Get session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
