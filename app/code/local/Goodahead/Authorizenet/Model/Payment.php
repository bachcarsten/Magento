<?php

class Goodahead_Authorizenet_Model_Payment
    extends Mage_Core_Model_Abstract
{
    /**
     * Init customer
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('goodahead_authorizenet/payment');
    }

    /**
     * Load by profile ID
     *
     * @param int $profileId
     * @return Goodahead_Authorizenet_Model_Payment
     */
    public function loadByProfileId($profileId)
    {
        $this->load($profileId, 'profile_id');
        return $this;
    }

    /**
     * Validate card
     *
     * @param Varien_Object $card
     * @return Goodahead_Authorizenet_Model_Payment
     * @throws Mage_Core_Exception
     */
    public function validate(Varien_Object $card)
    {
        $availableTypes = explode(',', $this->_getHelper()->getConfigData('cctypes'));

        $errorMsg = false;
        $ccNumber = $card->getCcNumber();
        $ccType   = $card->getCcType();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $card->setCcNumber($ccNumber);

        if (in_array($card->getCcType(), $availableTypes)){
            if ($this->validateCcNum($ccNumber)
                // Other credit card type number validation
                || ($this->OtherCcType($card->getCcType()) && $this->validateCcNumOther($ccNumber))) {

                $ccType = 'OT';
                $ccTypeRegExpList = array(
                    //Solo, Switch or Maestro. International safe
                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/', // Solo only
                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',

                    'SS'  => '/^((6759[0-9]{12})|(6334|6767[0-9]{12})|(6334|6767[0-9]{14,15})|(5018|5020|5038|6304|6759|6761|6763[0-9]{12,19})|(49[013][1356][0-9]{12})|(633[34][0-9]{12})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$/', // Maestro / Solo
                    'VI'  => '/^4[0-9]{12}([0-9]{3})?$/',             // Visa
                    'MC'  => '/^5[1-5][0-9]{14}$/',                   // Master Card
                    'AE'  => '/^3[47][0-9]{13}$/',                    // American Express
                    'DI'  => '/^6011[0-9]{12}$/',                     // Discovery
                    'JCB' => '/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/', // JCB
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
                    if (preg_match($ccTypeRegExp, $ccNumber)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }
                }

                if (!$this->OtherCcType($card->getCcType()) && $ccType != $card->getCcType()) {
                    $errorMsg  = $this->_getHelper()->__('Credit card number mismatch with credit card type.');
                }
            } else {
                $errorMsg  = $this->_getHelper()->__('Invalid Credit Card Number');
            }
        } else {
            $errorMsg  = $this->_getHelper()->__('Credit card type is not allowed for this payment method.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$card->getCcType()]) ? $verifcationRegEx[$card->getCcType()] : '';
            if (!$card->getCcCid() || !$regExp || !preg_match($regExp, $card->getCcCid())){
                $errorMsg = $this->_getHelper()->__('Please enter a valid credit card verification number.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($card->getCcExpYear(), $card->getCcExpMonth())) {
            $errorMsg = $this->_getHelper()->__('Incorrect credit card expiration date.');
        }

        if($errorMsg){
            Mage::throwException($errorMsg);
        }

        return $this;
    }

    /**
     * Validate expiration date
     *
     * @param string $expYear
     * @param string $expMonth
     * @return bool
     */
    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = Mage::app()->getLocale()->date();
        if (!$expYear || !$expMonth || ($date->compareYear($expYear)==1) || ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth)==1 )  )) {
            return false;
        }
        return true;
    }

    /**
     * Get verification ccv regExp
     *
     * @return array
     */
    public function getVerificationRegEx()
    {
        $verificationExpList = array(
            'VI' => '/^[0-9]{3}$/',       // Visa
            'MC' => '/^[0-9]{3}$/',       // Master Card
            'AE' => '/^[0-9]{4}$/',       // American Express
            'DI' => '/^[0-9]{3}$/',       // Discovery
            'SS' => '/^[0-9]{3,4}$/',
            'SM' => '/^[0-9]{3,4}$/',     // Switch or Maestro
            'SO' => '/^[0-9]{3,4}$/',     // Solo
            'OT' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{4}$/'       //JCB
        );
        return $verificationExpList;
    }

    /**
     * Has verification
     *
     * @return bool
     */
    public function hasVerification()
    {
        $configData = $this->_getHelper()->getConfigData('useccv');
        if(is_null($configData)){
            return true;
        }
        return (bool) $configData;
    }

    /**
     * Other cc type
     *
     * @param  $type
     * @return bool
     */
    public function OtherCcType($type)
    {
        return $type == 'OT';
    }

    /**
     * Other credit cart type number validation
     *
     * @param string $ccNumber
     * @return boolean
     */
    public function validateCcNumOther($ccNumber)
    {
        return preg_match('/^\\d+$/', $ccNumber);
    }

    /**
     * Validate credit card number
     *
     * @param   string $ccNumber
     * @return  bool
     */
    public function validateCcNum($ccNumber)
    {
        $cardNumber = strrev($ccNumber);
        $numSum = 0;

        for ($i=0; $i<strlen($cardNumber); $i++) {
            $currentNum = substr($cardNumber, $i, 1);

            /**
             * Double every second digit
             */
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }

            /**
             * Add digits of 2-digit numbers together
             */
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }

            $numSum += $currentNum;
        }

        /**
         * If the total has no remainder it's OK
         */
        return ($numSum % 10 == 0);
    }

    /**
     * Get helper
     *
     * @return Goodahead_Authorizenet_Helper_Config
     */
    protected function _getHelper()
    {
        return Mage::helper('goodahead_authorizenet/config');
    }


}