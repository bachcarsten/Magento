<?php

class Goodahead_PrivateSales_Model_System_Config_Source_Startup
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Goodahead_PrivateSales_Model_Privatesales::REDIRECT_LOGIN_PAGE,
                'label' => Mage::helper('goodahead_privatesales')->__('Customer login page'),
            ),
            array(
                'value' => Goodahead_PrivateSales_Model_Privatesales::REDIRECT_LANDING_PAGE,
                'label' => Mage::helper('goodahead_privatesales')->__('CMS page'),
            ),
        );
    }
}