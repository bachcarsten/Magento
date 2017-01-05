<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Goodahead_PdfReport_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{

    public function pdfinvoicesAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                ->setOrderFilter(array('in' => $orderIds))
                ->load();
            if ($invoices->getSize() > 0) {
                $reportBlock = $this->getLayout()
                    ->createBlock('goodahead_pdfreport/adminhtml_report_pdf');

                $reportBlock->addInvoices($invoices);

                $pdf = Mage::getModel('goodahead_pdfreport/pdf');
                return $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->getPdf($reportBlock), 'application/pdf');
            }
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');

    }

    public function pdfshipmentsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->setOrderFilter(array('in' => $orderIds))
                ->load();
            if ($shipments->getSize()) {
                $reportBlock = $this->getLayout()
                    ->createBlock('goodahead_pdfreport/adminhtml_report_pdf');
                $reportBlock->addShipments($shipments);
                $pdf = Mage::getModel('goodahead_pdfreport/pdf');
                return $this->_prepareDownloadResponse('packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->getPdf($reportBlock), 'application/pdf');
            }
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfcreditmemosAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addAttributeToSelect('*')
                ->setOrderFilter(array('in' => $orderIds))
                ->load();
            if ($creditmemos->getSize()) {
                $reportBlock = $this->getLayout()
                    ->createBlock('goodahead_pdfreport/adminhtml_report_pdf');
                $reportBlock->addCreditmemos($creditmemos);
                $pdf = Mage::getModel('goodahead_pdfreport/pdf');
                return $this->_prepareDownloadResponse('creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->getPdf($reportBlock), 'application/pdf');
            }
            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function pdfdocsAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            $reportBlock = $this->getLayout()
                ->createBlock('goodahead_pdfreport/adminhtml_report_pdf');
            $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->addAttributeToSelect('*')
                ->setOrderFilter(array('in' => $orderIds))
                ->addAttributeToSort('order_id', 'asc')
                ->load();
            if ($invoices->getSize() > 0) {
                $flag = true;
                $reportBlock->addInvoices($invoices);
            }
            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToSort('order_id', 'asc')
                ->setOrderFilter(array('in' => $orderIds))
                ->load();
            if ($shipments->getSize()) {
                $flag = true;
                $reportBlock->addShipments($shipments);
            }
            $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToSort('order_id', 'asc')
                ->setOrderFilter(array('in' => $orderIds))
                ->load();
            if ($creditmemos->getSize()) {
                $flag = true;
                $reportBlock->addCreditmemos($creditmemos);
            }

            if ($flag) {
                $pdf = Mage::getModel('goodahead_pdfreport/pdf');
                return $this->_prepareDownloadResponse('docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->getPdf($reportBlock), 'application/pdf');
            }

            $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }
}
