<?php

class Intermix_General_Model_Observer
{
	public function initGlobalCategory($observer)
	{
		$layer = Mage::getSingleton('catalog/layer');
        /* @var $layer Mage_Catalog_Model_Layer */
		$rootCategoryId = Mage::app()->getWebsite()->getDefaultStore()->getRootCategoryId();
        $rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
        try {
            Mage::register('current_category', $rootCategory);
            Mage::register('category', $rootCategory);
        } catch( Exception $e ) {
        	#pass
        }
        
        $layer->setCurrentCategory($rootCategory->getId());
	}
}