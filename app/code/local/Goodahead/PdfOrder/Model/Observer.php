<?php
class Goodahead_PdfOrder_Model_Observer
{
    public function blockPrepareOrderGrid($observer)
    {
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            $block->getMassactionBlock()->addItem('pdforders_order', array(
                'label'=> Mage::helper('goodahead_pdf_order')->__('Print Orders'),
                'url'  => Mage::helper('adminhtml')->getUrl('*/sales_order/pdforders'),
            ));
        }
    }
}