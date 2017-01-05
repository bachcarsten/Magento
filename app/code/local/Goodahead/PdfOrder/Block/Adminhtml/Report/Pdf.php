<?php
class Goodahead_PdfOrder_Block_Adminhtml_Report_Pdf
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Pdf
{
    public function addOrders($orders)
    {
        $this->append(
            $this->getLayout()
                ->createBlock('goodahead_pdf_order/adminhtml_report_sales_orders')
                ->setItems($orders->getItems())
        );
    }
}