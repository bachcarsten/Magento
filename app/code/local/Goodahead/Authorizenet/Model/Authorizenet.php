<?php

//require_once 'app/code/local/Goodahead/Authorizenet/sdk/AuthorizeNet.php';

class Goodahead_Authorizenet_Model_Authorizenet extends Varien_Object
{
    const ACTION_AUTHORIZE         = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';

    const AUTHORIZENET_VALIDATE_NONE = 'none';
    const AUTHORIZENET_VALIDATE_TEST = 'testMode';
    const AUTHORIZENET_VALIDATE_LIVE = 'liveMode';

    const WSDL_TEST = 'https://apitest.authorize.net/soap/v1/Service.asmx?WSDL';
    const WSDL_LIVE = 'https://api.authorize.net/soap/v1/Service.asmx?WSDL';

    /**
     * @var array
     */
    protected $_wsdl = array(
        'test' => self::WSDL_TEST,
        'live' => self::WSDL_LIVE
    );

    /**
     * @var array
     */
    protected $_validationMode = array(
        'none' => self::AUTHORIZENET_VALIDATE_NONE,
        'test' => self::AUTHORIZENET_VALIDATE_TEST,
        'live' => self::AUTHORIZENET_VALIDATE_LIVE
    );

    /**
     * @var SoapClient
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_merchantAuthentication = array();

    /**
     * Response
     *
     * @var array
     */
    protected $_response = array();

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        if (!defined('AUTHORIZENET_API_LOGIN_ID')) {
            define('AUTHORIZENET_API_LOGIN_ID', $this->getConfig('login'));
        }

        if (!defined('AUTHORIZENET_TRANSACTION_KEY')) {
            define('AUTHORIZENET_TRANSACTION_KEY', $this->getConfig('transaction_key'));
        }

        if (!defined('AUTHORIZENET_SANDBOX')) {
            define('AUTHORIZENET_SANDBOX', (bool) $this->getConfig('test_mode'));
        }

        if (!defined('AUTHORIZENET_LOG_FILE')) {
            define('AUTHORIZENET_LOG_FILE', dirname(__FILE__) . '/../sdk/report.log');
        }

        $this->_merchantAuthentication = array(
            'merchantAuthentication' => array(
                'name'           => $this->getConfig('login'),
                'transactionKey' => $this->getConfig('transaction_key')
            )
        );

        if ($this->getConfig('test_mode')) {
            $this->setClient($this->_wsdl['test']);
        } else {
            $this->setClient($this->_wsdl['live']);
        }
    }

    /**
     * Create customer and payment profiles
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param array $fields
     * @return bool|array
     */
    public function createCustomerAndPaymentProfiles(Goodahead_Authorizenet_Model_Customer $customer, $fields)
    {
        /*
        if (!$customer->getId()) {
            return false;
        }

        $paymentProfile = new AuthorizeNetPaymentProfile();
        $paymentProfile->customerType = 'individual';

        $creditCard = $paymentProfile->payment->creditCard;
        $creditCard->cardNumber = $fields['cc_number'];
        $creditCard->expirationDate = $fields['expiration_date'];

        $bill = array('firstName' => 'firstname', 'lastName' => 'lastname', 'company', 'address', 'city',
                      'state', 'zip', 'country', 'phoneNumber' => 'phone', 'faxNumber' => 'fax');
        foreach ($bill as $alias => $field) {
            if (empty($fields[$field])) {
                continue;
            }
            $method = is_string($alias) ? $alias : $field;
            $paymentProfile->billTo->{$method} = $fields[$field];
        }

        $customerProfileId = new AuthorizeNetCustomer();
        $customerProfileId->description = Mage::getBaseUrl();
        $customerProfileId->merchantCustomerId = $customer->getMerchantId();
        $customerProfileId->email = $customer->getEmail();
        $customerProfileId->paymentProfiles[] = $paymentProfile;

        $request = new AuthorizeNetCIM();

        $validation = $this->getConfig('test_mode') ? $this->_validationMode['test'] : $this->_validationMode['live'];
        Mage::log('validation: ' . $validation, null, 'authorizenet.log', true);
        $response = $request->createCustomerProfile($customerProfileId, $validation);

        $customerProfileId = null;
        $paymentProfileIds = array();

        if ($response->isOk()) {
            $customer->setProfileId($response->getCustomerProfileId());
            $customer->save();

            $payment = Mage::getModel('goodahead_authorizenet/payment');
            $payment->setAuthorizenetId($customer->getProfileId());
            $payment->setProfileId($response->getCustomerPaymentProfileIds());
            $payment->setType(isset($fields['cc_type']) ? $fields['cc_type'] : '');
            $payment->save();
            $paymentProfileIds[] = $payment->setProfileId();
        }

        $messages = new stdClass();
        $messages->text = $response->getMessageText();
        $result = array(
            'code'     => strtolower($response->getMessageCode()),
            'messages' => $messages,
            'customer_profile_id' => $customerProfileId,
            'payment_profile_ids' => $paymentProfileIds
        );
        return $result;
        */
        //
        //
        //

        $cardType = isset($fields['cc_type']) ? $fields['cc_type'] : '';
        $fields   = $this->_filterCustomerFields($fields);
        $options  = $this->_getOptionsForRequest($fields);
        $options['profile']['email'] = $customer->getEmail();
        $options['profile']['description'] = Mage::getBaseUrl();
        $options['profile']['merchantCustomerId'] = $customer->getNewMerchantId();

        $response = $this->getClient()->CreateCustomerProfile($options);

        $customerProfileId = null;
        $paymentProfileIds = array();

        if (strtolower($response->CreateCustomerProfileResult->resultCode) == 'ok') {
            $customerProfileId = $response->CreateCustomerProfileResult->customerProfileId;
            $customer->setProfileId($customerProfileId);
            $customer->setMerchantId($customer->getNewMerchantId());
            $customer->save();

            $payment = Mage::getModel('goodahead_authorizenet/payment');
            foreach ($response->CreateCustomerProfileResult->customerPaymentProfileIdList as $id) {
                $payment->setAuthorizenetId($customer->getProfileId());
                $payment->setProfileId($id);
                $payment->setType($cardType);
                $payment->save();
                $paymentProfileIds[] = $id;
            }
        }

        $result = array(
            'code'     => strtolower($response->CreateCustomerProfileResult->resultCode),
            'messages' => $response->CreateCustomerProfileResult->messages->MessagesTypeMessage,
            'customer_profile_id' => $customerProfileId,
            'payment_profile_ids' => $paymentProfileIds
        );
        return $result;
    }

    /**
     * Create payment profile
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param array $fields
     * @return bool|array
     */
    public function createPaymentProfile(Goodahead_Authorizenet_Model_Customer $customer, $fields)
    {
        /*
        if (!$customer->getProfileId()) {
            return false;
        }

        $paymentProfile = new AuthorizeNetPaymentProfile();
        $paymentProfile->customerType = 'individual';

        $creditCard = $paymentProfile->payment->creditCard;
        $creditCard->cardNumber = $fields['cc_number'];
        $creditCard->expirationDate = $fields['expiration_date'];

        $bill = array('firstName' => 'firstname', 'lastName' => 'lastname', 'company', 'address', 'city',
                      'state', 'zip', 'country', 'phoneNumber' => 'phone', 'faxNumber' => 'fax');
        foreach ($bill as $alias => $field) {
            if (empty($fields[$field])) {
                continue;
            }
            $method = is_string($alias) ? $alias : $field;
            $paymentProfile->billTo->{$method} = $fields[$field];
        }

        $request = new AuthorizeNetCIM();
        $validation = $this->getConfig('test_mode') ? $this->_validationMode['test'] : $this->_validationMode['live'];
        Mage::log('validation: ' . $validation, null, 'authorizenet.log', true);
        $response = $request->createCustomerPaymentProfile($customer->getProfileId(), $paymentProfile, $validation);

        if ($response->isOk()) {
            $payment = Mage::getModel('goodahead_authorizenet/payment');
            $payment->setAuthorizenetId($customer->getProfileId());
            $payment->setProfileId($response->getPaymentProfileId());
            $payment->setType(isset($fields['cc_type']) ? $fields['cc_type'] : '');
            $payment->save();
        }

        $messages = new stdClass();
        $messages->text = $response->getMessageText();
        $result = array(
            'code'               => strtolower($response->getMessageCode()),
            'messages'           => $messages,
            'payment_profile_id' => $response->getPaymentProfileId()
        );
        return $result;
        */


        //
        //
        //

        $cardType = isset($fields['card_type']) ? $fields['card_type'] : '';
        $fields   = $this->_filterPaymentFields($fields);
        $options  = $this->_getOptionsForRequest($fields);
        $options['customerProfileId'] = $customer->getProfileId();

        $paymentProfileId = null;
        $response = $this->getClient()->CreateCustomerPaymentProfile($options);
        if (strtolower($response->CreateCustomerPaymentProfileResult->resultCode) == 'ok') {
            $payment = Mage::getModel('goodahead_authorizenet/payment');
            $payment->setAuthorizenetId($customer->getProfileId());
            $payment->setProfileId($response->CreateCustomerPaymentProfileResult->customerPaymentProfileId);
            $payment->setType($cardType);
            $payment->save();
            $paymentProfileId = $response->CreateCustomerPaymentProfileResult->customerPaymentProfileId;
        }

        $result = array(
            'code'               => strtolower($response->CreateCustomerPaymentProfileResult->resultCode),
            'messages'           => $response->CreateCustomerPaymentProfileResult->messages->MessagesTypeMessage,
            'payment_profile_id' => $paymentProfileId
        );

        if ($result && $result['code'] == 'ok') {
            $this->_updateCustomerProfileMerchantId($customer);
        }

        return $result;
    }

    /**
     * Get customer payment profile
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param int $paymentProfileId
     * @return bool|array
     */
    public function getCustomerPaymentProfile(Goodahead_Authorizenet_Model_Customer $customer, $paymentProfileId)
    {
        if (!$customer->getProfileId()) {
            return false;
        }

        $options = array(
            'customerProfileId'        => $customer->getProfileId(),
            'customerPaymentProfileId' => $paymentProfileId,
            'unmaskExpirationDate'     => false
        );
        $options  = $this->_getOptionsForRequest($options);
        $response = $this->getClient()->GetCustomerPaymentProfile($options);
        $result   = array(
            'code'            => strtolower($response->GetCustomerPaymentProfileResult->resultCode),
            'messages'        => $response->GetCustomerPaymentProfileResult->messages->MessagesTypeMessage,
            'payment_profile' => $response->GetCustomerPaymentProfileResult->paymentProfile
        );

        return $result;
    }

    /**
     * Create authorize transaction
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param int $paymentProfileId
     * @param float $amount
     * @param array $items
     * @return Goodahead_Authorizenet_Model_Authorizenet
     * @throws Exception
     */
    public function createAuthorizationTransaction(Goodahead_Authorizenet_Model_Customer $customer, $paymentProfileId, $amount, array $items = array(), $orderIncrementId)
    {
        $this->_createTransaction('profileTransAuthOnly', $customer->getProfileId(), $paymentProfileId, $amount, $items, $orderIncrementId);
        return $this;
    }

    /**
     * Create authorization and capture transaction
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param int $paymentProfileId
     * @param float $amount
     * @param array $items
     * @return Goodahead_Authorizenet_Model_Authorizenet
     * @throws Exception
     */
    public function createAuthorizationAndCaptureTransaction(Goodahead_Authorizenet_Model_Customer $customer, $paymentProfileId, $amount, array $items = array(), $orderIncrementId)
    {
        $this->_createTransaction('profileTransAuthCapture', $customer->getProfileId(), $paymentProfileId, $amount, $items, $orderIncrementId);
        return $this;
    }

    /**
     * Create transaction
     *
     * @param string $type
     * @param int $customerProfileId
     * @param int $paymentProfileId
     * @param float $amount
     * @param array $items
     * @return stdClass
     */
    protected function _createTransaction($type, $customerProfileId, $paymentProfileId, $amount, array $items, $invoiceNumber)
    {
        $options = array(
            'transaction' => array(
                $type => array(
                    'amount'                   => $amount,
                    'customerProfileId'        => $customerProfileId,
                    'customerPaymentProfileId' => $paymentProfileId,
                    'lineItems'                => $items,
                    'order'                    => array('invoiceNumber' => $invoiceNumber)
                )
            )
        );
        $options  = $this->_getOptionsForRequest($options);
        $response = $this->getClient()->CreateCustomerProfileTransaction($options);
        if (strtolower($response->CreateCustomerProfileTransactionResult->resultCode) != 'ok') {
            throw new Mage_Core_Exception(Mage::helper('goodahead_authorizenet')->__('Cannot create transaction'));
        }
        return $response;
    }

    /**
     * Get payment profiles
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @return array|bool
     */
    public function getPaymentProfiles(Goodahead_Authorizenet_Model_Customer $customer)
    {
        if (!$customer->getProfileId()) {
            return false;
        }

        $paymentCollection = Mage::getModel('goodahead_authorizenet/payment')->getCollection();
        $paymentCollection->addFieldToFilter('authorizenet_id', array('eq' => $customer->getProfileId()));

        $result = array();
        foreach ($paymentCollection as $paymentProfile) {
            $profile = $this->getPaymentProfile($customer, $paymentProfile);
            if ($profile) {
                $result[$paymentProfile->getProfileId()] = $profile;
            }

            /*
            $options = array(
                'customerProfileId'        => $customer->getProfileId(),
                'customerPaymentProfileId' => $paymentProfile->getProfileId()
            );
            $options  = $this->_getOptionsForRequest($options);
            $response = $this->getClient()->GetCustomerPaymentProfile($options);
            if (strtolower($response->GetCustomerPaymentProfileResult->resultCode) != 'ok') {
                continue;
            }
            if (isset($response->GetCustomerPaymentProfileResult->paymentProfile->billTo)) {
                foreach ($response->GetCustomerPaymentProfileResult->paymentProfile->billTo as $name => $value) {
                    $result[$paymentProfile->getProfileId()]['billing'][$name] = $value;
                }
            }
            foreach ($response->GetCustomerPaymentProfileResult->paymentProfile->payment->creditCard as $name => $value) {
                $result[$paymentProfile->getProfileId()]['credit_card'][$name] = $value;
            }
             *
             */
        }
        return $result;
    }

    /**
     * Get payment profile
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param Goodahead_Authorizenet_Model_Payment $payment
     * @return array|bool
     */
    public function getPaymentProfile(Goodahead_Authorizenet_Model_Customer $customer, Goodahead_Authorizenet_Model_Payment $payment)
    {
        if (!$customer->getProfileId() || !$payment->getProfileId()) {
            return false;
        }

        $options = array(
            'customerProfileId'        => $customer->getProfileId(),
            'customerPaymentProfileId' => $payment->getProfileId(),
            'unmaskExpirationDate'     => false
        );

        $options  = $this->_getOptionsForRequest($options);
        $response = $this->getClient()->GetCustomerPaymentProfile($options);
        if (strtolower($response->GetCustomerPaymentProfileResult->resultCode) != 'ok') {
            return false;
        }

        $result = array();
        if (isset($response->GetCustomerPaymentProfileResult->paymentProfile->billTo)) {
            foreach ($response->GetCustomerPaymentProfileResult->paymentProfile->billTo as $name => $value) {
                $result['billing'][$name] = $value;
            }
        }
        foreach ($response->GetCustomerPaymentProfileResult->paymentProfile->payment->creditCard as $name => $value) {
            $result['credit_card'][$name] = $value;
        }

        return $result;
    }

    /**
     * Update payment profile
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @param Goodahead_Authorizenet_Model_Payment $payment
     * @param array $fields
     * @return bool|array
     */
    public function updatePaymentProfile(Goodahead_Authorizenet_Model_Customer $customer, Goodahead_Authorizenet_Model_Payment $payment, array $fields)
    {
        $profile = $this->getPaymentProfile($customer, $payment);
        if (!$profile) {
            return false;
        }

        $fields['customer_type'] = 'individual';
        $fields = $this->_filterPaymentFields($fields);

        foreach ($profile as $type => $group) {
            switch ($type) {
                /*
                case 'billing':
                    foreach ($group as $name => $value) {
                        if (!isset($fields['paymentProfile']['billTo'][$name])) {
                            $fields['paymentProfile']['billTo'][$name] = $value;
                        }
                    }
                    break;
                 */
                case 'credit_card':
                    foreach ($group as $name => $value) {
                        if (!isset($fields['paymentProfile']['payment']['creditCard'][$name])) {
                            $fields['paymentProfile']['payment']['creditCard'][$name] = $value;
                        }
                    }
                    break;
            }
        }

        $fields['customerProfileId'] = $customer->getProfileId();
        $fields['paymentProfile']['customerPaymentProfileId'] = $payment->getProfileId();

        $options  = $this->_getOptionsForRequest($fields);
        $response = $this->getClient()->UpdateCustomerPaymentProfile($options);

        $result   = array(
            'code'     => strtolower($response->UpdateCustomerPaymentProfileResult->resultCode),
            'messages' => $response->UpdateCustomerPaymentProfileResult->messages->MessagesTypeMessage
        );

        if ($result && $result['code'] == 'ok') {
            $this->_updateCustomerProfileMerchantId($customer);
        }

        return $result;
    }

    /**
     * Delete customer
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @return bool
     */
    public function deleteCustomer(Goodahead_Authorizenet_Model_Customer $customer)
    {
        if (!$customer->getProfileId()) {
            return false;
        }

        $options  = array('customerProfileId' => $customer->getProfileId());
        $options  = $this->_getOptionsForRequest($options);
        $response = $this->getClient()->DeleteCustomerProfile($options);
        if (strtolower($response->DeleteCustomerProfileResult->resultCode) != 'ok') {
            return false;
        }
        $customer->delete();
        return true;
    }

    /**
     * Delete Payment Profile
     *
     * @param Goodahead_Authorizenet_Model_Payment $payment
     * @return bool
     */
    public function deletePaymentProfile(Goodahead_Authorizenet_Model_Payment $payment)
    {
        if (!$payment->getAuthorizenetId() || !$payment->getProfileId()) {
            return false;
        }

        $options = array(
            'customerProfileId'        => $payment->getAuthorizenetId(),
            'customerPaymentProfileId' => $payment->getProfileId()
        );

        $options  = $this->_getOptionsForRequest($options);
        $response = $this->getClient()->DeleteCustomerPaymentProfile($options);
        if (strtolower($response->DeleteCustomerPaymentProfileResult->resultCode) != 'ok') {
            return false;
        }

        $payment->delete();
        return true;
    }

    /**
     * Filter payment fields
     *
     * @param array $fields
     * @return array
     */
    protected function _filterPaymentFields($fields)
    {
        if (!is_array($fields)) {
            return array();
        }

        $result = array();
        foreach ($fields as $name => $value) {
            $value = trim($value);
            if (!$value) {
                continue;
            }
            switch ($name) {
                case 'customer_profile_id':
                    $result['customerProfileId'] = $value;
                    break;
                case 'customer_type':
                    $result['paymentProfile']['customerType'] = $value;
                    break;
                case 'card_number':
                    $result['paymentProfile']['payment']['creditCard']['cardNumber'] = $value;
                    break;
                case 'expiration_date':
                    $result['paymentProfile']['payment']['creditCard']['expirationDate'] = $value;
                    break;
                case 'card_code':
                    $result['paymentProfile']['payment']['creditCard']['cardCode'] = $value;
                    break;
                case 'firstname':
                    $result['paymentProfile']['billTo']['firstName'] = $value;
                    break;
                case 'lastname':
                    $result['paymentProfile']['billTo']['lastName'] = $value;
                    break;
                case 'company':
                    $result['paymentProfile']['billTo']['company'] = $value;
                    break;
                case 'address':
                    $result['paymentProfile']['billTo']['address'] = $value;
                    break;
                case 'city':
                    $result['paymentProfile']['billTo']['city'] = $value;
                    break;
                case 'state':
                    $result['paymentProfile']['billTo']['state'] = $value;
                    break;
                case 'zip':
                    $result['paymentProfile']['billTo']['zip'] = $value;
                    break;
                case 'country':
                    $result['paymentProfile']['billTo']['country'] = $value;
                    break;
                case 'phone':
                    $result['paymentProfile']['billTo']['phoneNumber'] = $value;
                    break;
                case 'fax':
                    $result['paymentProfile']['billTo']['faxNumber'] = $value;
                    break;
            }
        }
        return $result;
    }

    /**
     * Filter customer fields
     *
     * @param array $fields
     * @return array
     */
    protected function _filterCustomerFields($fields)
    {
        if (!is_array($fields)) {
            return array();
        }

        $result = array();
        foreach ($fields as $name => $value) {
            $value = trim($value);
            if (!$value) {
                continue;
            }
            switch ($name) {
                case 'customer_type':
                    $result['profile']['paymentProfiles'][0]['customerType'] = $value;
                    break;
                case 'card_number':
                    $result['profile']['paymentProfiles'][0]['payment']['creditCard']['cardNumber'] = $value;
                    break;
                case 'expiration_date':
                    $result['profile']['paymentProfiles'][0]['payment']['creditCard']['expirationDate'] = $value;
                    break;
                case 'card_code':
                    $result['profile']['paymentProfiles'][0]['payment']['creditCard']['cardCode'] = $value;
                    break;
                case 'firstname':
                    $result['profile']['paymentProfiles'][0]['billTo']['firstName'] = $value;
                    break;
                case 'lastname':
                    $result['profile']['paymentProfiles'][0]['billTo']['lastName'] = $value;
                    break;
                case 'company':
                    $result['profile']['paymentProfiles'][0]['billTo']['company'] = $value;
                    break;
                case 'address':
                    $result['profile']['paymentProfiles'][0]['billTo']['address'] = $value;
                    break;
                case 'city':
                    $result['profile']['paymentProfiles'][0]['billTo']['city'] = $value;
                    break;
                case 'state':
                    $result['profile']['paymentProfiles'][0]['billTo']['state'] = $value;
                    break;
                case 'zip':
                    $result['profile']['paymentProfiles'][0]['billTo']['zip'] = $value;
                    break;
                case 'country':
                    $result['profile']['paymentProfiles'][0]['billTo']['country'] = $value;
                    break;
                case 'phone':
                    $result['profile']['paymentProfiles'][0]['billTo']['phoneNumber'] = $value;
                    break;
                case 'fax':
                    $result['profile']['paymentProfiles'][0]['billTo']['faxNumber'] = $value;
                    break;
            }
        }
        return $result;
    }

    /**
     * Get options
     *
     * @param array $options
     * @return array
     */
    protected function _getOptionsForRequest($options)
    {
        if (!is_array($options)) {
            $options = array();
        }

        if ($this->getConfig('test_mode')) {
            $options['validationMode'] = $this->_validationMode['test'];
        } else {
            $options['validationMode'] = $this->_validationMode['live'];
        }

        $options = array_merge_recursive($this->_merchantAuthentication, $options);
        return $options;
    }

    /**
     * Test authentication
     *
     * @return array
     */
    public function testAuthentication()
    {
        $response = $this->getClient()->AuthenticateTest($this->_merchantAuthentication);

        $result = array(
            'code'     => $response->AuthenticateTestResult->resultCode,
            'messages' =>  $response->AuthenticateTestResult->messages->MessagesTypeMessage,
        );

        return $result;
    }

    /**
     * Get API Config
     *
     * @param string $name
     * @param mixed $store
     * @return mixed
     */
    public function getConfig($name, $store = null)
    {
        return Mage::helper('goodahead_authorizenet/config')->getConfigData($name, $store);
    }

    /**
     * Get Client
     *
     * @return SoapClient
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Set Client
     * 
     * @param string $wsdl
     * @return Goodahead_Authorizenet_Model_Authorizenet
     */
    public function setClient($wsdl)
    {
        $this->_client = new SoapClient($wsdl);
        return $this;
    }

    /**
     * Update stored profile
     *
     * @param int $profileId
     * @param array $newData
     * @return bool|array
     */
    public function updateCustomerProfile($profileId, $newData)
    {
        if (!is_array($newData)) {
            return false;
        }
        $options = array(
            'profile' => array_merge($newData, array('customerProfileId' => $profileId))
        );
        $options = $this->_getOptionsForRequest($options);

        $response = $this->getClient()->UpdateCustomerProfile($options);

        $result = array(
            'code'     => strtolower($response->UpdateCustomerProfileResult->resultCode),
            'messages' => $response->UpdateCustomerProfileResult->messages->MessagesTypeMessage,
        );

        return $result;
    }

    /**
     * Update profile merchant id
     *
     * @param Goodahead_Authorizenet_Model_Customer $customer
     * @return $this
     */
    protected function _updateCustomerProfileMerchantId($customer)
    {
        if ($customer->getData('merchant_id') != $customer->getNewMerchantId()) {

            $newData = array(
                'merchantCustomerId' => $customer->getNewMerchantId()
            );

            $result = $this->updateCustomerProfile($customer->getProfileId(), $newData);

            if ($result && is_array($result)) {
                if ($result['code'] == 'ok') {
                    $customer->setMerchantId($customer->getNewMerchantId());
                    $customer->save();
                } else {
                    Mage::log(
                        Mage::helper('goodahead_authorizenet')
                            ->__('Authorize NET: UpdateCustomerProfile ERROR: ')
                        . print_r($result['messages'], true)
                    );
                }
            }
        }

        return $this;
    }
}