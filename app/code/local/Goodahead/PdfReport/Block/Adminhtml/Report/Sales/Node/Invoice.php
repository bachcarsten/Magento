<?php

class Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Invoice
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('goodahead/pdf/invoice.phtml');
    }

    public function setItem($item)
    {
        $item->load($item->getId());
        $this->setData('item', $item);
        return $this;
    }

    public function getInvoiceItems()
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
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