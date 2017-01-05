<?php
class Anowave_Ec_Block_Html extends Mage_Page_Block_Html
{
	public function getAbsoluteFooter()
	{
		$model = new Varien_Object(array
		(
			'footer' => parent::getAbsoluteFooter()
		));
		
		/**
		 * Notify listeners for absolute footer.
		 * This allows also other extensions to modify the absolute footer.
		 */
		Mage::dispatchEvent('absolute_footer',array
		(
			'model' => $model
		));
		
		return $model->getFooter();
	}
}