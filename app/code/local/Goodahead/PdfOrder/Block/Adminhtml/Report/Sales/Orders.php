<?php
class Goodahead_PdfOrder_Block_Adminhtml_Report_Sales_Orders
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_renderer = 'order';
        $this->_rendererBlock = Mage::getBlockSingleton(
            'goodahead_pdf_order/adminhtml_report_sales_node_' . $this->_renderer);
    }
}