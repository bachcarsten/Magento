<?php
/**
 * Description of
 * @package   CueBlocks_
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Francesco Magazzu' <francesco.magazzu at cueblocks.com>
 * @support   <magento at cueblocks.com>
 */


class CueBlocks_FreeAutoInvoice_Model_Observer
{
    protected $_config = null;

    public function getConfig($storeId = null)
    {
        if (!$this->_config) {
            $this->_config = Mage::helper('freeAutoInvoice')->getConfig($storeId);
        }
        return $this->_config;
    }

    public function autoInvoice($event)
    {
        $order = $event->getOrder();

        if ($enabled = $this->getConfig($order->getStoreId())->getEnabled()) {

            //if ($this->_getPaymentMethod($order) == 'free' && $order->getGrandTotal() == 0) {
                if ($order->canInvoice()) {
                    $this->_processOrderStatus($order);
                }
            //}
        }
        return $this;
    }

    private function _getPaymentMethod($order)
    {
        return $order->getPayment()->getMethodInstance()->getCode();
    }

    private function _processOrderStatus($order)
    {
        $config = $this->getConfig($order->getStoreId());

        // Prepare Invoice
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        // Set invoice to Payment
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        // Process Invoice
        $invoice->register();

//        $state = Mage_Sales_Model_Order::ACTION_FLAG_INVOICE;
//        $this->_changeOrderState($order, $state);

        // Add note to Order/Invoice History
        if ($orderNote = $config->getOrderNote()) {
            $invoice->addComment($orderNote);
            //$order->addStatusHistoryComment($orderNote, null);
            
            $order->setData('state', 'processing');
            $order->setStatus('processing'); 
            $history = $order->addStatusHistoryComment($orderNote, false);
            $history->setIsCustomerNotified(false);
        }
        // Save Order And Invoice using a transaction
        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        // Send Invoice Mail to the Customer
        if ($config->getEnabledEmail()) {
            if ($emailComment = $config->getEmailMessage()) {
                $invoice->sendEmail(true, $emailComment);
            } else {
                $invoice->sendEmail(true);
            }
        }

        return true;
    }

//    private function _changeOrderState($order, $state)
//    {
//        $order->setState($state, true);
//        $order->save();
//    }
}