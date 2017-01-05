<?php
class Goodahead_PdfOrder_Block_Adminhtml_Report_Sales_Node_Order
    extends Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('goodahead/pdf/order.phtml');
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getItem();
    }

    public function getOrderItems()
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $this->getItem();
        return $order->getAllItems();
    }

    /**
     * @param Mage_Sales_Model_Order $item
     * @return Goodahead_PdfOrder_Block_Adminhtml_Report_Sales_Node_Order
     */
    public function setItem($item)
    {
        $item->setOrder($item);
        $this->setData('item', $item);
        return $this;
    }
}