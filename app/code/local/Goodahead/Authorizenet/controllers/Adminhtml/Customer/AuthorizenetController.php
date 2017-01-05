<?php

class Goodahead_Authorizenet_Adminhtml_Customer_AuthorizenetController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Tab info
     *
     * @return void
     */
    public function infoAction()
    {
        $block = $this->getLayout()->createBlock('goodahead_authorizenet/adminhtml_customer_info')->toHtml();
        $this->getResponse()->setBody($block);
    }

    /**
     * Render payment
     *
     * @return void
     */
    public function paymentAction()
    {
        try {
            $profileId = $this->getRequest()->getQuery('profile_id');
            $customer  = $this->getAuthorizenetCustomer();
            $payment   = $this->getPaymentProfile($profileId);

            $authorizenet = $this->getAuthorizenet();
            $result       = $authorizenet->getPaymentProfile($customer, $payment);

            $block = $this->getLayout()->createBlock('goodahead_authorizenet/adminhtml_customer_info_payment');
            $block->setProfile($profileId, $result);
            $this->getResponse()->setBody($block->toHtml());
        } catch (Exception $e) {
        }
    }

    /**
     * Delete payment
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function deleteAction()
    {
        $profileId = (int) $this->getRequest()->getParam('profile_id');
        $payment   = $this->getPaymentProfile($profileId);
        if (!$payment->getId()) {
            return $this->sendJson(array('success' => false, 'message' => 'Payment ID is empty'));
        }

        $authorizenet = $this->getAuthorizenet();
        $result = $authorizenet->deletePaymentProfile($payment);
        if ($result === true) {
            return $this->sendJson(array('success' => true, 'profile_id' => $profileId, 'message' => 'Payment has been deleted'));
        } else {
            return $this->sendJson(array('success' => false, 'profile_id' => $profileId, 'message' => 'Cannot delete payment'));
        }
    }

    /**
     * Save payment profile
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('authorizenet');
        if (!is_array($data) || empty($data['profile'])) {
            return $this->sendJson(array('success' => false, 'message' => 'Data is empty'));
        }

        $values = array();
        foreach ($data['profile'] as $name => $value) {
            $value = trim($value);
            if ($value) {
                $values[$name] = $value;
            }
        }

        if (isset($values['id'])) {
            return $this->update($values);
        } else {
            return $this->create($values);
        }
    }

    /**
     * Update payment profile
     *
     * @param array $values
     * @return Zend_Controller_Response_Abstract
     */
    public function update($values)
    {
        if (isset($values['cc_exp_month'], $values['cc_exp_year'])) {
            $values['expiration_date'] = $values['cc_exp_year']
                . '-'
                . (strlen($values['cc_exp_month']) < 2 ? '0' . $values['cc_exp_month'] : $values['cc_exp_month']);
            unset($values['cc_exp_month'], $values['cc_exp_year']);
        }

        try {
            $authorizenetCustomer = $this->getAuthorizenetCustomer();
            $paymentProfile       = $this->getPaymentProfile($values['id']);

            $authorizenet = $this->getAuthorizenet();
            $result = $authorizenet->updatePaymentProfile($authorizenetCustomer, $paymentProfile, $values);
            if ($result['code'] == 'ok') {
                return $this->sendJson(array(
                    'success'    => true,
                    'profile_id' => $values['id'],
                    'type'       => 'updated',
                    'message'    => $this->__('Payment Profile was updated')
               ));
            } else {
                return $this->sendJson(array(
                    'success' => false,
                    'message' => $result['messages']->code . ' ' . $result['messages']->text
                ));
            }
        } catch (Exception $e) {
            return $this->sendJson(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Create payment profile
     *
     * @param array $values
     * @return Zend_Controller_Response_Abstract
     */
    public function create($values)
    {
        try {
            $object = new Varien_Object($values);
            $object->setCcNumber($object->getCardNumber());
            $object->setCcType($object->getCcType());
            $object->setCcCid($object->getCardCode());
            $paymentProfile = $this->getPaymentProfile();
            $paymentProfile->validate($object);
        } catch (Exception $e) {
            return $this->sendJson(array('success' => false, 'message' => $e->getMessage()));
        }

        if (isset($values['cc_exp_month'], $values['cc_exp_year'])) {
            $values['expiration_date'] = $values['cc_exp_year']
                . '-'
                . (strlen($values['cc_exp_month']) < 2 ? '0' . $values['cc_exp_month'] : $values['cc_exp_month']);
            unset($values['cc_exp_month'], $values['cc_exp_year']);
        }

        $authorizenetCustomer = $this->getAuthorizenetCustomer();
        try {
            $authorizenet = $this->getAuthorizenet();
            if ($authorizenetCustomer->getProfileId()) {
                $result = $authorizenet->createPaymentProfile($authorizenetCustomer, $values);
            } else {
                $result = $authorizenet->createCustomerAndPaymentProfiles($authorizenetCustomer, $values);
            }

            if ($result['code'] == 'ok') {
                $profileId = isset($result['payment_profile_id']) ? $result['payment_profile_id'] : $result['payment_profile_ids'][0];
                return $this->sendJson(array(
                    'success'    => true,
                    'profile_id' => $profileId,
                    'type'       => 'created',
                    'message'    => $this->__('Payment Profile was created successfully')
                ));
            } else {
                return $this->sendJson(array(
                    'success' => false,
                    'message' => $result['messages']->code . ' ' . $result['messages']->text
                ));
            }
        } catch (Exception $e) {
            return $this->sendJson(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * Get authorizenet customer
     *
     * @return Goodahead_Authorizenet_Model_Customer
     */
    public function getAuthorizenetCustomer()
    {
        /** @var Goodahead_Authorizenet_Model_Customer $customer */
        $customer = Mage::getModel('goodahead_authorizenet/customer');
        $customer->loadByCustomerId($this->getRequest()->getParam('customer_id'));
        if (!$customer->getCustomerId()) {
            $customer->setCustomerId($this->getRequest()->getParam('customer_id'));
            $customer->save();
        }
        return $customer;
    }

    /**
     * Get payment profile
     *
     * @param null|int $profileId
     * @return Goodahead_Authorizenet_Model_Payment
     */
    public function getPaymentProfile($profileId = null)
    {
        $paymentProfile = Mage::getModel('goodahead_authorizenet/payment');
        if ($profileId) {
            $paymentProfile->loadByProfileId($profileId);
        }
        return $paymentProfile;
    }

    /**
     * Get authorize.net model
     *
     * @return Goodahead_Authorizenet_Model_Authorizenet
     */
    public function getAuthorizenet()
    {
        return Mage::getModel('goodahead_authorizenet/authorizenet');
    }

    /**
     * Send JSON
     *
     * @param array $data
     * @return Zend_Controller_Response_Abstract
     */
    public function sendJson(array $data)
    {
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }
}