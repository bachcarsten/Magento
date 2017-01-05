<?php
class Goodahead_CreditBalance_Model_Credit extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_TEMPLATE               = 'creditbalance/credit/email_template';
    const XML_PATH_EMAIL_IDENTITY               = 'creditbalance/credit/email_sender';

    protected function _construct()
    {
        $this->_init('goodahead_creditbalance/credit');
    }

    public function loadByCustomerId($customerId)
    {
        return $this->load($customerId, 'customer_id');
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        if (Mage::helper('goodahead_creditbalance')->canSendEmail()) {
            $customer = $this->getCustomer();
            try {
                $this->_sendNotificationEmail($customer);
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Send credit notification email
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Goodahead_CreditBalance_Model_Credit
     */
    protected function _sendNotificationEmail($customer)
    {
        $storeId = Mage::app()->getStore()->getId();

        // Retrieve corresponding email template id and customer name
        $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
        $customerName = $customer->getName();

        /** @var Mage_Core_Model_Email_Info $emailInfo  */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($customer->getEmail(), $customerName);

        /** @var Mage_Core_Model_Email_Template_Mailer $mailer  */
        $mailer = Mage::getModel('core/email_template_mailer');
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'customer'    => $customer,
                'credit'      => $this
            )
        );
        $mailer->send();

        return $this;
    }
}