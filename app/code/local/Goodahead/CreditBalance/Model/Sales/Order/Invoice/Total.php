<?php
class Goodahead_CreditBalance_Model_Sales_Order_Invoice_Total extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice  $invoice)
    {
        parent::collect($invoice);
        $baseAmount = $invoice->getOrder()->getBaseAccountCredit();
        $amount = $invoice->getOrder()->getAccountCredit();
        if ($amount > 0) {
            $invoice->setGrandTotal($invoice->getGrandTotal() - $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseAmount);
        }
    }
}