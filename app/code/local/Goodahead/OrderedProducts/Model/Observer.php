<?php

class Goodahead_OrderedProducts_Model_Observer
{
    public function customerSaveAfter($observer)
    {
        $customer = $observer->getCustomer();
        $products = Mage::app()->getRequest()->getPost('goodahead_orderedproducts', null);
        if ($products !== null) {
            $products = Mage::helper('adminhtml/js')->decodeGridSerializedInput($products);
            $data = array();
            foreach ($products as $id => $item) {
                $data[] = array(
                    'product_id' => $id,
                    'position'   => $item['position'],
                );
            }
            Mage::getModel('goodahead_orderedproducts/products')->bulkSave($customer->getId(), $data);
        }
    }
    
    public function orderPlaceAfter($observer)
    {
        $order = $observer->getOrder();
        $items = $order->getAllVisibleItems();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if($customerId){
            $data = Mage::getModel('goodahead_orderedproducts/products')->loadArray($customerId);  
        
            foreach($items as $item){
                $data1 = array(
                    'product_id' => $item->getProductId(),
                    'position'   => 0,
                );
                $flag = false;
                foreach($data as $d){
                    if($d['product_id'] == $data1['product_id']){
                        $flag = true;
                        break;
                    }
                }
                if(!$flag) array_push($data, $data1);
            }    
            Mage::getModel('goodahead_orderedproducts/products')->bulkSave($customerId, $data);
        }
        
        
        
//        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
//        $data = array();
//        foreach ($items as $item) {
//            $data[] = array(
//                'product_id' => $item->getProductId(),
//                'position'   => 0,
//            );
//        }
        
    }
    
}