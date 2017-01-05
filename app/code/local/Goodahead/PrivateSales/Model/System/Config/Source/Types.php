<?php

class Goodahead_PrivateSales_Model_System_Config_Source_Types
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Goodahead_PrivateSales_Model_Privatesales::TYPE_LOGIN,
                'label' => Mage::helper('goodahead_privatesales')->__('Allow Login Only'),
            ),
            array(
                'value' => Goodahead_PrivateSales_Model_Privatesales::TYPE_LOGIN_REGISTER,
                'label' => Mage::helper('goodahead_privatesales')->__('Allow Register and Login'),
            ),
        );
    }
}