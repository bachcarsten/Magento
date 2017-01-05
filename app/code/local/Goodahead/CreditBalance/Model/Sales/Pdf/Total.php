<?php
class Goodahead_CreditBalance_Model_Sales_Pdf_Total extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->getAccountCredit();
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        $totals = array(array(
            'amount'    => Mage::helper('core')->currency(-$amount),
            'label'     => Mage::helper('goodahead_creditbalance')->__('Account credit') . ':',
            'font_size' => $fontSize
        ));
        return $totals;
    }

    public function getAmount()
    {
        return $this->getOrder()->getAccountCredit();
    }
}
