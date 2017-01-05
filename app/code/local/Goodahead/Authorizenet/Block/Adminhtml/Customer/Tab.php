<?php

class Goodahead_Authorizenet_Block_Adminhtml_Customer_Tab
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return string
     */
    public function getClass()
    {
        return 'ajax';
    }

    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('*/customer_authorizenet/info', array('_current' => true));
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('AuthorizeNet CIM');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        $this->__('AuthorizeNet CIM');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}