<?php
class Goodahead_Sales_Block_Adminhtml_Sales_Order_Invoice_Create_Items
    extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
{
    public function canEditQty()
    {
        if ($this->getInvoice()->getOrder()->getPayment()->canCapture()) {
            if (
                $this->getInvoice()->getOrder()->getPayment()->getMethod() == 'goodahead_authorizenet' ||
                $this->getInvoice()->getOrder()->getPayment()->getMethod() == 'authorizenet'
            ) {
                return true;
            }
            return $this->getInvoice()->getOrder()->getPayment()->canCapturePartial();
        }
        return true;
    }
}