<?php

class Goodahead_Authorizenet_Model_PaymentMethod
    extends Mage_Payment_Model_Method_Cc
{
    protected $_code          = 'goodahead_authorizenet';
    protected $_formBlockType = 'goodahead_authorizenet/form_cc';
    protected $_canAuthorize  = true;
    protected $_canCapture    = true;

    /**
     * @var Goodahead_Authorizenet_Model_Customer
     */
    protected $_authorizenetCustomer = null;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
            ->setPaymentProfileId($data->getPaymentProfileId());
        return $this;
    }

    /**
     * Validate method
     *
     * @return Goodahead_Authorizenet_Model_PaymentMethod
     */
    public function validate()
    {
        $info = $this->getInfoInstance();

        if ($info->getPaymentProfileId()) {
            $this->_checkIfPaymentValid($info);
            return $this;
        }

        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            $hash = $info->getCcNumber()
                  . $info->getExpMonth()
                  . $info->getExpYear()
                  . $info->getCcCid();
            $hash = md5($hash);
            $ccHashes = (array) $info->getAdditionalInformation('cc_hashes');
            if (!isset($ccHashes[$hash])) {
                parent::validate();
                $this->_changeProfile($info);
                $ccHashes[$hash] = $info->getAdditionalInformation('payment_profile_id');
                $info->setAdditionalInformation('cc_hashes', $ccHashes);
            }
        }

        return $this;
    }

    /**
     * Check if payment valid
     *
     * @param Mage_Payment_Model_Info $info
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _checkIfPaymentValid(Mage_Payment_Model_Info $info)
    {
        $authorizenetCustomer = $this->_getAuthorizenetCustomer($info);

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorizenet
         */
        $authorizenet = Mage::getModel('goodahead_authorizenet/authorizenet');
        $response = $authorizenet->getCustomerPaymentProfile($authorizenetCustomer, $info->getPaymentProfileId());
        if (false === $response) {
            throw new Mage_Core_Exception(Mage::helper('goodahead_authorizenet')->__('Customer not valid'));
        }

        if ($response['code'] != 'ok') {
            $this->_throwException($response['messages']);
        }

        $info->setAdditionalInformation('payment_profile_id', $info->getPaymentProfileId());
        $info->setAdditionalInformation('customer_profile_id', $this->_getAuthorizenetCustomer($info)->getProfileId());

        /* for progress */
        $payment = Mage::getModel('goodahead_authorizenet/payment')->load($info->getPaymentProfileId(), 'profile_id');
        $info->setCcType($payment->getType());
        $info->setCcLast4(substr($response['payment_profile']->payment->creditCard->cardNumber, -4));

        /* update billing address if needed */
        if ($info->getQuote() instanceof Mage_Sales_Model_Quote) {
            $billingAddress  = $this->_getBillingAddress($info);
            $newBillingHash  = md5(serialize($billingAddress));
            $prevBillingHash = $info->getAdditionalInformation('billing_address_hash');

            if ($newBillingHash != $prevBillingHash) {
                $response = $authorizenet->updatePaymentProfile(
                    $this->_getAuthorizenetCustomer($info),
                    $payment,
                    $billingAddress
                );
                if ($response['code'] != 'ok') {
                    $this->_throwException($response['messages']);
                } else {
                    $info->setAdditionalInformation('billing_address_hash', $newBillingHash);
                }
            }
        }


        return true;
    }

    /**
     * Change profile
     *
     * @param Mage_Payment_Model_Info $info
     * @return Goodahead_Authorizenet_Model_PaymentMethod
     * @throws Mage_Core_Exception
     */
    protected function _changeProfile(Mage_Payment_Model_Info $info)
    {
        $fields               = $this->_getFields($info);
        $authorizenetCustomer = $this->_getAuthorizenetCustomer($info);

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorizenet
         */
        $authorizenet         = Mage::getModel('goodahead_authorizenet/authorizenet');

        if ($authorizenetCustomer->getProfileId()) {
            $response = $authorizenet->createPaymentProfile($authorizenetCustomer, $fields);
            if ($response['code'] == 'ok') {
                $info->setAdditionalInformation('customer_profile_id', $authorizenetCustomer->getProfileId());
                $info->setAdditionalInformation('payment_profile_id', $response['payment_profile_id']);
            }
        } else {
            $response = $authorizenet->createCustomerAndPaymentProfiles($authorizenetCustomer, $fields);
            if ($response['code'] == 'ok') {
                $info->setAdditionalInformation('customer_profile_id', $authorizenetCustomer->getProfileId());
                $info->setAdditionalInformation('payment_profile_id', $response['payment_profile_ids'][0]);
            }
        }

        if ($response['code'] != 'ok') {
            $this->_throwException($response['messages']);
        }

        $paymentProfileId = $info->getAdditionalInformation('payment_profile_id');
        $payment = Mage::getModel('goodahead_authorizenet/payment')->load($paymentProfileId, 'profile_id');
        $payment->setType($info->getCcType());
        $payment->save();

        return $this;
    }

    /**
     * Throw exception
     *
     * @param array|stdClass $messages
     * @throws Mage_Core_Exception
     * @return void
     */
    protected function _throwException($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                throw new Mage_Core_Exception($message->text);
            }
        }

        throw new Mage_Core_Exception($messages->text);
    }

    /**
     * Get authorizenet customer
     *
     * @param Mage_Payment_Model_Info $info
     * @return Goodahead_Authorizenet_Model_Customer
     */
    protected function _getAuthorizenetCustomer(Mage_Payment_Model_Info $info)
    {
        if (null === $this->_authorizenetCustomer) {
            if ($profileId = $info->getAdditionalInformation('customer_profile_id')) {
                $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer')->load($profileId, 'profile_id');
                if ($authorizenetCustomer->getId()) {
                    $this->_authorizenetCustomer = $authorizenetCustomer;
                    return $this->_authorizenetCustomer;
                }
            }

            $customerId = null;
            if ($info instanceof Mage_Sales_Model_Quote_Payment) {
                $customerId = $info->getQuote()->getCustomerId();
            } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
                $customerId = $info->getQuote()->getCustomerId();
            }

            $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer');
            if ($customerId) {
                $authorizenetCustomer->loadByCustomerId($customerId);
            }

            if (!$authorizenetCustomer->getId()) {
                $authorizenetCustomer->setCustomerId($customerId);
                $authorizenetCustomer->save();
            }

            $this->_authorizenetCustomer = $authorizenetCustomer;
        }

        return $this->_authorizenetCustomer;
    }

    /**
     * Get fields
     *
     * @param Mage_Payment_Model_Info $info
     * @return array
     */
    protected function _getFields(Mage_Payment_Model_Info $info)
    {
        $fields = array(
            'customer_type'   => 'individual',
            'card_number'     => $info->getCcNumber(),
            'expiration_date' => $info->getCcExpYear() . '-'
                . (strlen($info->getCcExpMonth()) < 2 ? '0' . $info->getCcExpMonth() : $info->getCcExpMonth())
        );

        $fields += $this->_getBillingAddress($info);

        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        /*
        $quote = $info->getQuote();
        if ($quote instanceof Mage_Sales_Model_Quote) {
            $billingAddress = $quote->getBillingAddress();
            $address = $billingAddress->getStreet();
            if (is_array($address)) {
                $address = $address[0];
            }

            $fields['firstname'] = $billingAddress->getFirstname();
            $fields['lastname']  = $billingAddress->getLastname();
            $fields['company']   = $billingAddress->getCompany();
            $fields['address']   = $address;
            $fields['city']      = $billingAddress->getCity();
            $fields['state']     = $billingAddress->getRegion();
            $fields['zip']       = $billingAddress->getPostcode();
            $fields['country']   = $billingAddress->getCountry();
            $fields['phone']     = $billingAddress->getTelephone();
            $fields['fax']       = $billingAddress->getFax();
        }*/

        return $fields;
    }

    /**
     * Get billing address
     *
     * @param Mage_Payment_Model_Info $info
     * @return array
     */
    protected function _getBillingAddress(Mage_Payment_Model_Info $info)
    {
        $fields = array();

        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = $info->getQuote();
        if ($quote instanceof Mage_Sales_Model_Quote) {
            $billingAddress = $quote->getBillingAddress();
            $address = $billingAddress->getStreet();
            if (is_array($address)) {
                $address = $address[0];
            }

            $fields['firstname'] = $billingAddress->getFirstname();
            $fields['lastname']  = $billingAddress->getLastname();
            $fields['company']   = $billingAddress->getCompany();
            $fields['address']   = $address;
            $fields['city']      = $billingAddress->getCity();
            $fields['state']     = $billingAddress->getRegion();
            $fields['zip']       = $billingAddress->getPostcode();
            $fields['country']   = $billingAddress->getCountry();
            $fields['phone']     = $billingAddress->getTelephone();
            $fields['fax']       = $billingAddress->getFax();
        }

        return $fields;
    }

    /**
     * Authorization transaction
     *
     * @param Varien_Object $payment
     * @param  $amount
     * @return Goodahead_Authorizenet_Model_PaymentMethod
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        return $this->_transaction('authorize', $payment, $amount);
    }

    /**
     * Capture
     *
     * @param Varien_Object $payment
     * @param  $amount
     * @return Goodahead_Authorizenet_Model_PaymentMethod
     */
    public function capture(Varien_Object $payment, $amount)
    {
        return $this->_transaction('capture', $payment, $amount);
    }

    /**
     * Create transaction
     *
     * @param string $mode
     * @param Varien_Object $payment
     * @param float $amount
     * @return Goodahead_Authorizenet_Model_PaymentMethod
     * @throws Exception|Mage_Core_Exception
     */
    protected function _transaction($mode, Varien_Object $payment, $amount)
    {
        $paymentProfileId     = $payment->getAdditionalInformation('payment_profile_id');
        $authorizenetCustomer = $this->_getAuthorizenetCustomer($payment);

        $items = array();
        foreach ($payment->getOrder()->getAllVisibleItems() as $item) {
            $items[] = array(
                'itemId'      => $item->getProductId(),
                'name'        => $item->getName(),
                'description' => $item->getDescription(),
                'unitPrice'   => $item->getPrice(),
                'quantity'    => $item->getQtyOrdered()
            );
        }

        switch ($mode) {
            case 'authorize':
                $method = 'createAuthorizationTransaction';
                break;
            case 'capture':
                $method = 'createAuthorizationAndCaptureTransaction';
                break;
            default:
                throw new Exception('Wrong method');
        }

        /**
         * @var Goodahead_Authorizenet_Model_Authorizenet $authorizenet
         */
        $authorizenet = Mage::getModel('goodahead_authorizenet/authorizenet');
        $orderIncrementId = $payment->getOrder()->getIncrementId();
        try {
            $authorizenet->$method($authorizenetCustomer, $paymentProfileId, $amount, $items, $orderIncrementId);
        } catch (Exception $e) {
            throw new Mage_Core_Exception($e->getMessage());
        }

        if ($customerId = $payment->getOrder()->getCustomerId()) {
            if (!$authorizenetCustomer->getCustomerId()) {
                $authorizenetCustomer->setCustomerId($customerId);
                $authorizenetCustomer->save();
            }
        } else {
            $authorizenet->deleteCustomer($authorizenetCustomer);
        }

        return $this;
    }

    /**
     * Refund
     *
     * @param Varien_Object $payment
     * @param  $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        return parent::refund($payment, $amount);
    }

    /**
     * Void
     *
     * @param Varien_Object $payment
     * @return Mage_Payment_Model_Abstract
     */
    public function void(Varien_Object $payment)
    {
        return parent::void($payment);
    }
}