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
 * @package     Mage_Loworderfee
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mango_Loworderfee_Model_Total_Invoice_Loworderfee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        if (!$order->getLoworderfee()){
            return $this;
        }

        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getLofAmount() && !$previusInvoice->isCanceled()) {
                $includeLofTax = false;
            }
        }

        $baseLoworderfee = $order->getBaseLoworderfee();
        $baseLofInvoiced = $order->getBaseLofInvoiced();
        $baseInvoiceTotal = $invoice->getBaseGrandTotal();
        $loworderfee = $order->getLoworderfee();
        $loworderfeeInvoiced = $order->getLofInvoiced();
        $invoiceTotal = $invoice->getGrandTotal();

        if (!$baseLoworderfee || $baseLofInvoiced==$baseLoworderfee) {
            return $this;
        }

        $baseLofToInvoice = $baseLoworderfee - $baseLofInvoiced;
        $loworderfeeToInvoice = $loworderfee - $loworderfeeInvoiced;

        $baseInvoiceTotal = $baseInvoiceTotal + $baseLofToInvoice;
        $invoiceTotal = $invoiceTotal + $loworderfeeToInvoice;

        $invoice->setBaseGrandTotal($baseInvoiceTotal);
        $invoice->setGrandTotal($invoiceTotal);

        $invoice->setBaseLoworderfee($baseLofToInvoice);
        $invoice->setLoworderfee($loworderfeeToInvoice);

        $order->setBaseLofInvoiced($baseLofInvoiced+$baseLofToInvoice);
        $order->setLofInvoiced($loworderfeeInvoiced+$loworderfeeToInvoice);

        return $this;
    }

}
