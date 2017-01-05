<?php
class Anowave_Ec_Model_System_Config_Format
{
	public function toOptionArray()
	{
		return array
		(
			array
			(
				'value' => 1, 
				'label' => Mage::helper('ec')->__('1-line notification to visitors')
			
			),
			array
			(
				'value' => 2, 
				'label' => Mage::helper('ec')->__('2-line notification to visitors')
			),
			array
			(
				'value' => 3, 
				'label' => Mage::helper('ec')->__('No notification to visitors')
			),
		);
	}
}