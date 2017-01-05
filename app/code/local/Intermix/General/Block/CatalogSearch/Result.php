<?php

class Intermix_General_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    protected function _prepareLayout()
    {
        $title = $this->__("Search results", $this->helper('catalogSearch')->getEscapedQueryText());

        // add Home breadcrumb
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => $title,
                'title' => $title
            ));
        }
        $this->getLayout()->getBlock('head')->setTitle($title);
        
        return $this;
    }
}