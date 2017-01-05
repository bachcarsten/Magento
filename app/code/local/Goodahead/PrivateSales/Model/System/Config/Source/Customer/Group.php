<?php

class Goodahead_PrivateSales_Model_System_Config_Source_Customer_Group
{
    public function toOptionArray()
    {
    	$model = Mage::getModel('customer/group')->getCollection();
        return $model->toOptionArray();
    }
}