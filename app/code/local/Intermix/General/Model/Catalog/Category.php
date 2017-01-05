<?php

class Intermix_General_Model_Catalog_Category extends Mage_Catalog_Model_Category 
{
    /**
     * Retrieve header image URL
     *
     * @return string
     */
    public function getHeaderImageUrl()
    {
        $url = false;
        if ($image = $this->getHeadImage()) {
            $url = Mage::getBaseUrl('media').'catalog/category/'.$image;
        }
        return $url;
    }
}