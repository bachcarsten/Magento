<?php
class Goodahead_GoogleShopping_Model_Observer
{
    public function productCollectionLoadBefore($observer)
    {
        $key = Mage::registry('goodahead_prepare_google_shopping_block');

        if ($key === true) {
            /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection = $observer->getCollection();
            $collection->addAttributeToSelect('status');
        }
    }

    public function blockToHtmlBefore($observer)
    {
        /** @var $block Mage_GoogleShopping_Block_Adminhtml_Items_Product */
        $block = $observer->getBlock();

        if ($block instanceof Mage_GoogleShopping_Block_Adminhtml_Items_Product) {
            Mage::register('goodahead_prepare_google_shopping_block', true, true);

            $block->addColumnAfter('status',
                array(
                    'header'=> Mage::helper('goodahead_google_shipping')->__('Status'),
                    'width' => '60px',
                    'index' => 'status',
                    'type'  => 'options',
                    'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
                ), 'type'
            );
        } elseif ($block instanceof Mage_GoogleShopping_Block_Adminhtml_Items_Item) {
            $block->addColumnAfter('product_id',
                array(
                    'header' => Mage::helper('goodahead_google_shipping')->__('Product ID'),
                    'index'  => 'product_id',
                    'width'  => '60px',
                ), 'expires');
        }
    }
}
