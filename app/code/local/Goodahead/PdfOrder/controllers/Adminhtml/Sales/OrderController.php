<?php
class Goodahead_PdfOrder_Adminhtml_Sales_OrderController
    extends Mage_Adminhtml_Controller_Action
{
    public function pdfordersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');

        if (!empty($orderIds)) {
            /** @var $orders Mage_Sales_Model_Resource_Order_Collection */
            $orders = Mage::getModel('sales/order')->getCollection();
            $orders->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in'=>$orderIds))
                ->load();

            if ($orders->getSize() > 0) {
                $reportBlock = $this->getLayout()
                    ->createBlock('goodahead_pdf_order/adminhtml_report_pdf');

                $reportBlock->addOrders($orders);

                $pdf = Mage::getModel('goodahead_pdfreport/pdf');
                return $this->_prepareDownloadResponse('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->getPdf($reportBlock), 'application/pdf');
            }

            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
        }

        $this->_redirect('*/*/');
    }
}