<?php

class Intermix_General_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    public function getUrl()
    {
        $query = array(
            $this->getFilter()->getRequestVar()=>$this->getValue(),
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $urlString = 'catalogsearch/result/index';
        $block = Mage::app()->getLayout()->getBlock('catalog.leftnav');
        if( is_object($block) && $block->getFilterUrl() ) {
            $urlString = $block->getFilterUrl();
        }
        
//        if( Mage::registry('current_category') && Mage::registry('current_category')->getLevel() > 1 ) {
//            $query['cat'] = Mage::registry('current_category')->getId();
//        }
        
        return Mage::getUrl($urlString, array('_current'=>true, '_query'=>$query));
    }
}