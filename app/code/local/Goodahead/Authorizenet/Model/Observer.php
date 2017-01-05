<?php

class Goodahead_Authorizenet_Model_Observer
{
    /**
     * Customer delete before
     *
     * @param Varien_Event_Observer $event
     * @return Goodahead_Authorizenet_Model_Observer
     */
    public function customerDeleteBefore(Varien_Event_Observer $event)
    {
        $customer = $event->getCustomer();

        $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer')->loadByCustomerId($customer->getId());
        if ($authorizenetCustomer->getId()) {
            $customer->setAuthorizenetCustomer($authorizenetCustomer);
        }

        return $this;
    }

    /**
     * Customer delete commit after
     *
     * @param Varien_Event_Observer $event
     * @return Goodahead_Authorizenet_Model_Observer
     * @throws Exception
     */
    public function customerDeleteCommitAfter(Varien_Event_Observer $event)
    {
        $authorizenetCustomer = $event->getCustomer()->getAuthorizenetCustomer();
        if ($authorizenetCustomer instanceof Goodahead_Authorizenet_Model_Customer) {
            try {
                Mage::getModel('goodahead_authorizenet/authorizenet')->deleteCustomer($authorizenetCustomer);
            } catch (Exception $e) {}
        }

        return $this;
    }
}