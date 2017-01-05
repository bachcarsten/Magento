<?php
class Goodahead_Sales_Model_Observer
{
    public function adminhtmlSalesOrderInvoiceSavePostdispatch($observer)
    {
        $params = $observer->getControllerAction()->getRequest()->getParams();

        /** @var Mage_Sales_Model_Order_Invoice $invoice  */
        $invoice = Mage::registry('current_invoice');
        if (isset($params['invoice']['is_final']) && $params['invoice']['is_final']) {
            $order = $invoice->getOrder();
            foreach ($order->getAllItems() as $item) {
                $item->cancel();
            }
            $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
            $order->save();
        }
    }
}