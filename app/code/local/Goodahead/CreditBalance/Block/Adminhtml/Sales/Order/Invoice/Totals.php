<?php
class Goodahead_CreditBalance_Block_Adminhtml_Sales_Order_Invoice_Totals
    extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
{
    public function initTotals()
    {
        $amount = $this->getOrder()->getAccountCredit();
        $baseAmount = $this->getOrder()->getBaseAccountCredit();
        $this->getInvoice();
        if ($amount > 0) {
            $parent = $this->getParentBlock();
            $credit = new Varien_Object(array(
                'code'  => 'credit_balance',
                'value' => - $amount,
                'base_value'=> - $baseAmount,
                'label' => Mage::helper('goodahead_creditbalance')->__('Account credit')
            ));
            $parent->addTotal($credit, 'last');
        }
        return $this;
    }
}