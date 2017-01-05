<?php

class Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Creditmemo
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('goodahead/pdf/creditmemo.phtml');
    }

    public function getCreditmemoItems()
    {
        /* @var $invoice Mage_Sales_Model_Order_Creditmemo */
        $invoice = $this->getItem();
        return $invoice->getAllItems();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }



}