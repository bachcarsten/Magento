<?php

class Goodahead_PdfReport_Block_Adminhtml_Report_Pdf
    extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('goodahead/pdf/pdf.phtml');
    }

    public function addShipments($shipments)
    {
        $this->append(
            $this->getLayout()
                ->createBlock('goodahead_pdfreport/adminhtml_report_sales_shipments')
                ->setItems($shipments->getItems())
        );
    }

    public function addInvoices($invoices)
    {
        $this->append(
            $this->getLayout()
                ->createBlock('goodahead_pdfreport/adminhtml_report_sales_invoices')
                ->setItems($invoices->getItems())
        );
    }

    public function addCreditmemos($creditmemos)
    {
        $this->append(
            $this->getLayout()
                ->createBlock('goodahead_pdfreport/adminhtml_report_sales_creditmemos')
                ->setItems($creditmemos->getItems())
        );
    }
}