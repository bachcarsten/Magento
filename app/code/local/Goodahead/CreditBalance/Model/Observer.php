<?php
class Goodahead_CreditBalance_Model_Observer
{
    public function saveCreditData($observer)
    {
        if (Mage::helper('goodahead_creditbalance')->isEnabled()) {
            $request = $observer->getControllerAction()->getRequest();
            $creditId = $request->getParam('credit_id');
            $credit = Mage::getModel('goodahead_creditbalance/credit')->load($creditId);
            $customer = Mage::registry('current_customer');

            $credit->addData(array(
                'id' => $creditId,
                'customer_id' => $customer->getId(),
                'balance'  => $request->getParam('credit_balance'),
                'enabled'  => $request->getParam('credit_enabled'),
                'customer' => $customer,
            ));
            $credit->save();
        }
    }

    public function onOrderPlaceAfter($observer)
    {
        if (Mage::helper('goodahead_creditbalance')->isEnabled()) {
            $order = $observer->getOrder();
            if ($order->getAccountCredit()) {
                Mage::getModel('goodahead_creditbalance/credit')
                    ->loadByCustomerId($order->getCustomerId())
                    ->setBalance(0)
                    ->save();
            }
        }
    }
}