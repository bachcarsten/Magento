<?php

class Intermix_General_Model_Sales_Order_Pdf_Total_Discount extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix().$amount;
        }
        
        if( $couponCode = $this->getOrder()->getCouponCode() ) {
        	$coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        	$rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
			if( $rule->getName() ) {
        		$label = Mage::helper('sales')->__($this->getTitle()) . ' ('. $rule->getName() .'):';
			}
        }

        if( !isset($label) ) {
        	$label = Mage::helper('sales')->__($this->getTitle()) . ':';
        }
        
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = array(
            'amount'    => $amount,
            'label'     => $label,
            'font_size' => $fontSize
        );
        return array($total);
    }
}