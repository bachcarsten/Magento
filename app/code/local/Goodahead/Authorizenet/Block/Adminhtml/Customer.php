<?php

class Goodahead_Authorizenet_Block_Adminhtml_Customer
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Add tab
     *
     * @return void
     */
    public function addTab()
    {
        if (Mage::registry('current_customer')->getId()) {
            $this->getParentBlock()->addTab('goodahead_authorizenet_customer_tab', 'goodahead_authorizenet/adminhtml_customer_tab');
        }
    }
}