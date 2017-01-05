<?php
class Anowave_Ec_Model_System_Config_Currency
{
	public function toOptionArray()
	{
		return array
		(
			array
			(
				'value' => 'EUR', 
				'label' => Mage::helper('ec')->__('€ Euro')
			
			),
			array
			(
				'value' => 'USD', 
				'label' => Mage::helper('ec')->__('$ US Dollar')
			),
			array
			(
				'value' => 'GBP', 
				'label' => Mage::helper('ec')->__('£ British pound')
			),
		);
	}
}