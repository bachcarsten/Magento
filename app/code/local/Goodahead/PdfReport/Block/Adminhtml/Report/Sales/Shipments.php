<?php

class Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Shipments
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->_renderer = 'shipment';
    }
}