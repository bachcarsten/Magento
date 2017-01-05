<?php

class Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Shipment
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('goodahead/pdf/shipment.phtml');
    }

    public function getShipmentItems()
    {
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $this->getItem();
        return $shipment->getAllItems();
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $this->getItem();
        return $shipment->getOrder();
    }



}