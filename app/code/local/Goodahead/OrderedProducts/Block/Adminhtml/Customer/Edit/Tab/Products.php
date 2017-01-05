<?php

class Goodahead_OrderedProducts_Block_Adminhtml_Customer_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return 'Order Form Products';
    }

    public function getTabTitle()
    {
        return 'Order Form Products';
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function getTabClass()
    {
        return 'ajax';
    }

    public function getSkipGenerateContent()
    {
        return true;
    }

    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/goodahead_orderedproducts/grid', array('_current' => true));
    }
}