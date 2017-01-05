<?php

class EM_DeleteOrder_Model_Observer
{
    public function addMassAction($observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            $block->getMassactionBlock()->addItem('delete_order', array(
                'label'=> Mage::helper('sales')->__('Delete order'),
                'url'  => $block->getUrl('*/sales_order/deleteorder'),
            ));
        }
    }
}