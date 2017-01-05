<?php

class Goodahead_OrderedProducts_Block_Recent extends Mage_Catalog_Block_Product_Abstract
{
    protected $_products;
    protected $_limit = 5;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getProducts()
    {
        if( !$this->_products ) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            /* @var $ordersCollection Mage_Sales_Model_Entity_Order_Collection */
            //$ordersCollection = Mage::getModel('sales/order')->getCollection()
//                                        ->addFieldToFilter('customer_id', $customerId)
//                                        ->load();
//     
//            $orderIds = array();
//            foreach ($ordersCollection as $_order) {
//                $orderIds[] = $_order->getId();
//            }
//            
//            /* @var $orderItemsCollection Mage_Sales_Model_Entity_Order_Item_Collection */
//            $orderItemsCollection = Mage::getModel('sales/order_item')->getCollection()
//                                             ->addFieldToFilter('order_id', array('in' => $orderIds))
//                                             ->setOrder('item_id', 'DESC');
//    
//            $productsIds = array();
//            foreach( $orderItemsCollection as $_item ) {
//                $productsIds[$_item->getProductId()] = $_item->getProductId();
//            }
            $productsIds = array();
            $suggestedProducts = Mage::getModel('goodahead_orderedproducts/products')->loadArray($customerId);
            for($i = 0; $i < count($suggestedProducts)-1; $i ++){
                for($j = $i+1; $j < count($suggestedProducts); $j ++){
                    if($suggestedProducts[$i]['position'] > $suggestedProducts[$j]['position']){
                        $temp = $suggestedProducts[$i];
                        $suggestedProducts[$i] = $suggestedProducts[$j];
                        $suggestedProducts[$j] = $temp;
                    }
                }
            }
            foreach ($suggestedProducts as $suggestedProduct) {
                $productsIds[$suggestedProduct['product_id']] = $suggestedProduct['product_id'];
            }

            /* @var $productCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
            $productCollection = Mage::getModel('catalog/product')->getCollection()
                                    ->addAttributeToSelect('*')
                                    ->addMinimalPrice()
                                    ->addFinalPrice()
                                    ->addTaxPercents()
                                    ->setPageSize($this->_getLimit());

            $productCollection->addUrlRewrite();
        
            $productCollection->addIdFilter($productsIds);
            foreach( $productsIds as $_id ) {
                if( $_product = $productCollection->getItemById($_id) ) {
                    $this->_products[$_id] = $_product;
                }
            }
        }
        
        return $this->_products;
    }
    
    protected function _getLimit()
    {
        return $this->_limit;
    }
    
    public function getAddUrl()
    {
        return $this->getUrl('customer/history/add');
    }
    
    protected function _getPriceBlockTemplate($productTypeId)
    {
        return 'goodahead_orderedproducts/price.phtml';
    }

    public function _preparePriceRenderer($productType)
    {
        $block = parent::_preparePriceRenderer($productType);
        $block->setDisplayTierQty($this->getDisplayTierQty());
        $block->setTierQty($this->getTierQty());

        return $block;
    }

    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '', $displayTierQty = false)
    {
        if ($displayTierQty === true) {
            $product->setTierPrice(null);
            $tierPrices = $this->getTierPrices($product);
            $tierQty = null;
            foreach ($tierPrices as $tierPrice) {
                if ($tierPrice['price'] == $product->getMinimalPrice()) {
                    $tierQty = $tierPrice['price_qty'];
                    break;
                }
            }
            $this->setTierQty($tierQty);
        }
        $this->setDisplayTierQty($displayTierQty);
        return parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
    }


}