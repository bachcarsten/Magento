<?php
class Goodahead_CreditBalance_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    public function initTotals()
    {
        if ($this->getSource()->getAccountCredit() > 0) {
            $total = new Varien_Object(array(
                'code'      => 'credit_balance',
                'value'     => - $this->getSource()->getAccountCredit(),
                'base_value'=> - $this->getSource()->getBaseAccountCredit(),
                'label'     => $this->__('Account credit')
            ));
            $this->getParentBlock()->addTotal($total);
        }
        return $this;
    }
}