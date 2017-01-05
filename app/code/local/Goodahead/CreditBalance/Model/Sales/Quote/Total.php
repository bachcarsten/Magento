<?php
class Goodahead_CreditBalance_Model_Sales_Quote_Total extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        if ($address->getData('address_type') =='billing') {
            return $this;
        }

        $baseAmount = Mage::getModel('goodahead_creditbalance/credit')
            ->loadByCustomerId($address->getCustomerId())->getBalance();
        $amount = Mage::app()->getStore()->convertPrice($baseAmount);
        if ($address->getSubtotal() > $amount) {
            $address->getQuote()->setBaseAccountCredit($baseAmount);
            $address->getQuote()->setAccountCredit($amount);
            $amount = -$amount;
            $baseAmount = -$baseAmount;
            $this->_setAmount((float)$amount);
            $this->_setBaseAmount($baseAmount);
            $address->setCreditAmount($amount);
        } elseif ($address->getQuote()->getBaseAccountCredit()) {
            $address->getQuote()->unsBaseAccountCredit();
            $address->getQuote()->unsAccountCredit();
        }
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getCreditAmount();
        if ($amount < 0) {
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => Mage::helper('goodahead_creditbalance')->__('Account credit'),
                'value' => $amount,
            ));
        }
        return $this;
    }
}