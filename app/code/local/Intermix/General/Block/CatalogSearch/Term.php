<?php

class Intermix_General_Block_CatalogSearch_Term extends Mage_CatalogSearch_Block_Term 
{
    protected function _loadTerms()
    {
        if (empty($this->_terms)) {
            $this->_terms = array();

            $terms = Mage::getResourceModel('catalogsearch/query_collection')
                ->setPopularQueryFilter(Mage::app()->getStore()->getId())
                ->addFieldToFilter('query_text', array('neq' => '%'))
                ->setOrder('popularity', 'DESC')
                ->setPageSize(100)
                ->load()
                ->getItems();

            if( count($terms) == 0 ) {
                return $this;
            }


            $this->_maxPopularity = reset($terms)->getPopularity();
            $this->_minPopularity = end($terms)->getPopularity();
            $range = $this->_maxPopularity - $this->_minPopularity;
            $range = ( $range == 0 ) ? 1 : $range;
            foreach ($terms as $term) {
                if( !$term->getPopularity() ) {
                    continue;
                }
                $term->setRatio(($term->getPopularity()-$this->_minPopularity)/$range);
                $this->_terms[$term->getName()] = $term;
            }
            ksort($this->_terms);
        }
        return $this;
    }
}