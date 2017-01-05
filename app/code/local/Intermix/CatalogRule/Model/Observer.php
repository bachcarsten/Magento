<?php

class Intermix_CatalogRule_Model_Observer extends Mage_CatalogRule_Model_Observer
{
    /**
     * Apply catalog price rules to product on frontend
     *
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processFrontFinalPrice($observer)
    {
        $product    = $observer->getEvent()->getProduct();
        $pId        = $product->getId();
        $storeId    = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = Mage::app()->getLocale()->storeTimeStamp($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
        }

		if ($observer->hasCustomerGroupId()) {
		    $gId = $observer->getEvent()->getCustomerGroupId();
		} elseif ($product->hasCustomerGroupId()) {
		    $gId = $product->getCustomerGroupId();
		    if( !$gId ) {
		    	$gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		    }
		} else {
		    $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		}

        $key = "$date|$wId|$gId|$pId";
        if (!isset($this->_rulePrices[$key])) {
            $rulePrice = Mage::getResourceModel('catalogrule/rule')
                ->getRulePrice($date, $wId, $gId, $pId);
            $this->_rulePrices[$key] = $rulePrice;
        }
        if ($this->_rulePrices[$key]!==false) {
            $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }
}