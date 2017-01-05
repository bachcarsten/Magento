<?php

class Goodahead_Authorizenet_Block_Account_Tab
    extends Mage_Core_Block_Template
{
    /**
     * Add tab
     * 
     * @return void
     */
    public function addTab()
    {
        $helper = $this->helper('goodahead_authorizenet/config');
        if ($helper->isEnabled()) {
            $navigation = $this->getParentBlock();
            $navigation->addLink('goodahead_authorizenet_account', 'goodahead_authorizenet/account/', $this->__('Stored Payment Info'));
        }
    }
}