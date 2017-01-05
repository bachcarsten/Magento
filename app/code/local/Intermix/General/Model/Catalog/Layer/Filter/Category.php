<?php

class Intermix_General_Model_Catalog_Layer_Filter_Category extends Mage_Catalog_Model_Layer_Filter_Category
{
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_SUBCATEGORIES';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $category   = $this->getCategory();
            
            if( $category->getLevel() == 1 ) {
                $_categoriesLevel3 = $category->getChildrenCategories()->getItems();
                $category = array_shift($_categoriesLevel3);
                $this->getLayer()->getProductCollection()
                    ->addCategoryFilter($category);
            }
            /** @var $category Mage_Catalog_Model_Categeory */
            $categories = $category->getChildrenCategories();

            $this->getLayer()->getProductCollection()
                ->addCountToCategories($categories);

            $data = array();
            foreach ($categories as $category) {
                if ($category->getIsActive() && $category->getProductCount()) {
                    $data[] = array(
                        'label' => $category->getName(),
                        'value' => $category->getId(),
                        'count' => $category->getProductCount(),
                    );
                }
            }
            
            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    } 
}