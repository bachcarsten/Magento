<?php
class Goodahead_ProductDiscount_Model_Observer
{
    public function getProductFinalPrice($observer)
    {
        $product = $observer->getProduct();
        $qty = $observer->getQty();

        $flag = Mage::getStoreConfig('productdiscount/options/use_tier_price_level');

        if (Mage::registry('goodahead_get_product_final_price')) {
            Mage::unregister('goodahead_get_product_final_price');
            return;
        }

        if ($flag && !is_null($qty)) {
            Mage::register('goodahead_get_product_final_price', true);
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ((int)$quote->getItemsQty()) {
                $qty = $quote->getItemsQty();
                $finalPrice = $product->getPriceModel()->getFinalPrice($qty, $product);
            }
        }
    }
}