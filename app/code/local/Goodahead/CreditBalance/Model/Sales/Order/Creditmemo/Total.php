<?php
class Goodahead_CreditBalance_Model_Sales_Order_Creditmemo_Total
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo  $creditmemo)
    {
        parent::collect($creditmemo);
        $baseAmount = $creditmemo->getOrder()->getBaseAccountCredit();
        $amount = $creditmemo->getOrder()->getAccountCredit();
        if ($amount > 0) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseAmount);
        }
    }
}