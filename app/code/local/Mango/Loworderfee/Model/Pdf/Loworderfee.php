<?php


class Mango_Loworderfee_Model_Pdf_Loworderfee extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();        
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

//        if($amount>0){
        
            $totals = array(array(
                'amount'    => $this->getAmountPrefix().$amount,
                'label'     => Mage::getStoreConfig('sales/minimum_order/low_order_fee_title', $store->getId()) . ':',
                'font_size' => $fontSize
            ));
        

        return $totals;
//        }
    }
}
