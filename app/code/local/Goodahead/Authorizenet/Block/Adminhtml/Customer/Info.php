<?php

class Goodahead_Authorizenet_Block_Adminhtml_Customer_Info
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'goodahead/authorizenet/customer/info.phtml';

    /**
     * Payment profiles
     *
     * @var array|bool
     */
    protected $_paymentProfiles;

    protected $_authorizenetCustomer;

    /**
     * Get payment profiles
     *
     * @return bool|array
     */
    public function getPaymentProfiles()
    {
        if ($this->_paymentProfiles !== null) {
            return $this->_paymentProfiles;
        }

        $authorizenetCustomer = $this->getAuthorizenetCustomer();
        if (!$authorizenetCustomer->getId()) {
            $this->_paymentProfiles = false;
        } else {
            /**
             * @var Goodahead_Authorizenet_Model_Authorizenet $authorize
             */
            $authorize = Mage::getModel('goodahead_authorizenet/authorizenet');
            $this->_paymentProfiles = $authorize->getPaymentProfiles($authorizenetCustomer);
        }

        return $this->_paymentProfiles;
    }

    /**
     * Get authorizenet customer
     *
     * @return Goodahead_Authorizenet_Model_Customer
     */
    public function getAuthorizenetCustomer()
    {
        if ($this->_authorizenetCustomer === null) {
            $authorizenetCustomer = Mage::getModel('goodahead_authorizenet/customer');
            $authorizenetCustomer->loadByCustomerId($this->getCustomerId());
            $this->_authorizenetCustomer = $authorizenetCustomer;
        }
        return $this->_authorizenetCustomer;
    }

    /**
     * Render profiles
     *
     * @return string
     */
    public function renderPaymentProfiles()
    {
        $html = '';
        if ($paymentProfiles = $this->getPaymentProfiles()) {
            foreach ($paymentProfiles as $id => $paymentProfile) {
                $block = $this->getLayout()->createBlock('goodahead_authorizenet/adminhtml_customer_info_payment');
                $block->setProfile($id, $paymentProfile);
                $html .= $block->toHtml();
            }
        }
        return $html;
    }

    /**
     * Render form
     *
     * @return string
     */
    public function renderForm()
    {
        return $this->getLayout()
            ->createBlock('goodahead_authorizenet/adminhtml_customer_info_form')
            ->toHtml();
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId()
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * Get delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete/customer_id/' . $this->getRequest()->getParam('id'));
    }

    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save/customer_id/' . $this->getRequest()->getParam('id'));
    }

    /**
     * Get payment url
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        return $this->getUrl('*/*/payment/customer_id/' . $this->getRequest()->getParam('id'));
    }
}