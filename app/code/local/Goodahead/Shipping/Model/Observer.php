<?php
class Goodahead_Shipping_Model_Observer
{
    public function prepareLayoutBefore($observer)
    {
        /** @var $block Mage_Adminhtml_Block_Sales_Order_View */
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {

            $found = false;
            /** @var $order Mage_Sales_Model_Order */
            $order = $this->getOrder();
            foreach ($order->getShipmentsCollection() as $shipping) {
                if ($shipping->getShippingLabel()) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $orderIds = array(
                    $order->getId(),
                );
                $url = Mage::helper('adminhtml')->getUrl('*/sales_order_shipment/massPrintShippingLabel', array('_query' => array('order_ids' => $orderIds, 'massaction_prepare_key' => 'order_ids')));

                $block->addButton('print_shipping_labels', array(
                    'label'     => Mage::helper('goodahead_shipping')->__('Print Shipping Labels'),
                    'onclick'   => 'setLocation(\'' . $url . '\')',
                ));
            }
        }
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }
}