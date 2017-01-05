<?php
class Goodahead_General_Model_Observer
{
    public function addCustomAttributeToQuoteItem(Varien_Event_Observer $observer)
    {
        if ($categoryId = (int)Mage::app()->getRequest()->getParam('category')) {
            /* @var $quoteItem Mage_Sales_Model_Quote_Item */
            $quoteItem = $observer->getEvent()->getQuoteItem();

            if (!$quoteItem->isDeleted() && !$quoteItem->getParentItemId()) {
                $quoteItem->setCategoryId($categoryId);
            }
        }
    }

    public function setCustomProductChildPricesBlock(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();

        $confAttributes = $product->getTypeInstance()->getConfigurableAttributes();

        $attrPricesCount = array();
        $countprices = 0;

        if ($confAttributes) {
            foreach($confAttributes as $attribute) {
               $attrPricesCount[] = count($attribute->getPrices());
               $countprices += count($attribute->getPrices());
            }
        }

        $childIds = Mage::getModel('catalog/product_type_configurable')
            ->getChildrenIds($product->getId());

        $childIds = array_values($childIds[0]);

        $i = 0;
        foreach($childIds as $key => $curChild) {
            $i++;
            if ($i/8 > 1) {
                unset($childIds[$key]);
                array_unshift($childIds, $curChild);
            }
        }

        $childIds = array_values($childIds);



        $runIterator = (int)Mage::registry('runIterator');

        if ($runIterator<=0) {
            $runIterator=1;
            Mage::register('runIterator',$runIterator);

        } else {
            $runIterator++;
            Mage::unregister('runIterator');
            Mage::register('runIterator',$runIterator);
        }


        if ($runIterator/$countprices > 1) {
            Mage::unregister('pr_iterator');
            Mage::unregister('attrNumberiterator');
            Mage::unregister('runIterator');
        }

        $iterator = (int)Mage::registry('pr_iterator');

        if ($iterator<=0) {
            $iterator=1;
            Mage::register('pr_iterator',$iterator);

        } else {
            $iterator++;
            Mage::unregister('pr_iterator');
            Mage::register('pr_iterator',$iterator);
        }

        $attrNumberiterator = (int)Mage::registry('attrNumberiterator');

        if ($attrNumberiterator<=0) {
            $attrNumberiterator=1;
            Mage::register('attrNumberiterator',$attrNumberiterator);

        } else {
            if ((isset($attrPricesCount[$attrNumberiterator]))
                && ($attrPricesCount[$attrNumberiterator] == count($childIds))
                && ($iterator > $attrPricesCount[$attrNumberiterator-1])) {

                $attrNumberiterator++;
                Mage::unregister('attrNumberiterator');
                Mage::register('attrNumberiterator',$attrNumberiterator);
            }
        }

        if ($iterator>count($childIds)) {
            $iterator=1;
            Mage::unregister('pr_iterator');
            Mage::register('pr_iterator',$iterator);
        }

        if ($attrPricesCount[$attrNumberiterator-1] == count($childIds)) {
            $realProduct = Mage::getModel('catalog/product')->load($childIds[$iterator-1]);

            $product->setConfigurablePrice($realProduct->getFinalPrice() - $product->getFinalPrice());
        } else {
            $product->setConfigurablePrice(0);
        }
    }

    /**
     * @return Mage_Core_Helper_Data
    */
    protected function _getCoreHelper()
    {
        return Mage::helper('core');
    }
}