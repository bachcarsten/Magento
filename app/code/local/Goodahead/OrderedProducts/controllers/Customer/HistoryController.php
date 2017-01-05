<?php

class Goodahead_OrderedProducts_Customer_HistoryController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }    

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('catalog')->__('Home'),
                'title'=>Mage::helper('catalog')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));
            $breadcrumbsBlock->addCrumb('reorder_form', array(
                'label'=>Mage::helper('catalog')->__('My Order Form'),
                'title'=>Mage::helper('catalog')->__('My Order Form'),
            ));
        }
        $this->renderLayout();
    }
    
    public function addAction()
    {
        $messages   = array();
        $errors     = array();

        $qtyArray   = $this->getRequest()->getPost('qty');
        $productIds = array();
        $redirectUrl= Mage::getUrl('customer/history');
        
        if( is_array($qtyArray) ) {
            foreach( $qtyArray as $_productId => $_qty ) {
                if( $_qty > 0 ) {
                    $productIds[] = $_productId;
                }
            }
        }

        if( count($productIds) <= 0 ) {
            $this->_getSession()->addError(Mage::helper('goodahead_orderedproducts')->__('Sorry, no items has been added to your shopping cart.'));
            $this->_redirectUrl($redirectUrl);
            return $this;
        }

        /* @var $productCollection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $productCollection = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('entity_id', array('in' => $productIds));

        Mage::getModel('cataloginventory/stock')->addItemsToProducts($productCollection);

        /* @var $cart Mage_Checkout_Model_Cart */
        $cart = Mage::getSingleton('checkout/cart');
        $quote = $cart->getQuote(); // BC Custom Code

        foreach ($productCollection as $_product) {
            if(!isset($qtyArray[$_product->getId()])
                || (isset($qtyArray[$_product->getId()])
                    && (int)$qtyArray[$_product->getId()] <= 0) ) {
                continue;
            }

            /**************** BC Custom Code ****************/
            $stockItem = $_product->getStockItem();
            if(!$stockItem->getIsInStock()) {
                $errors[] = Mage::helper('goodahead_orderedproducts')->__('Unable to add "%s" to your shopping cart due to the following reason: %s', $_product->getName(), 'This product is currently out of stock.');
                continue;
            }
            $quoteItem = Mage::getModel('sales/quote_item')->getCollection()
                        ->setQuote($quote)
                        ->addFieldToFilter('quote_id', $quote->getId())
                        ->addFieldToFilter('product_id', $_product->getId())
                        ->getFirstItem();
            if($qtyArray[$_product->getId()] > ($stockItem->getQty() - $quoteItem->getQty())) {
                $errors[] = Mage::helper('goodahead_orderedproducts')->__('Unable to add "%s" to your shopping cart due to the following reason: The requested quantity for "%s" is not available.', $_product->getName(), $_product->getName());
                continue;
            }
            /**************** End BC Custom Code ****************/

            try {
                $cart->addProduct($_product, array('qty' => (int)$qtyArray[$_product->getId()]));
                
                Mage::dispatchEvent('checkout_cart_add_product_complete',
                    array('product' => $_product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                );

                $messages[] = Mage::helper('goodahead_orderedproducts')->__('Product "%s" has been added to your shopping cart.', $_product->getName());
            } catch (Mage_Core_Exception $e) {
                $errors[] = Mage::helper('goodahead_orderedproducts')->__('Unable to add "%s" to your shopping cart due to the following reason: %s', $_product->getName(), $e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $errors[] = Mage::helper('goodahead_orderedproducts')->__('Cannot add the item "%s" to shopping cart.', $_product->getName());
            }
        }

        if (count($messages)) {
            foreach ($messages as $_message) {
                $this->_getSession()->addSuccess($_message);
            }
        }

        if (count($errors)) {
            foreach ($errors as $_error) {
                $this->_getSession()->addError($_error);
            }
            $cart->save(); // BC Custom Code
        } else {
            try {
                $cart->getQuote()->setTotalsCollectedFlag(false);
                $cart->save();
                $redirectUrl = Mage::getUrl('checkout/cart');
                $this->_getSession()->setCartWasUpdated(true);
            } catch (Exception $e) {
                Mage::logException($e);

                /* @var $customerSession Mage_Customer_Model_Session */
                $customerSession = Mage::getSingleton('customer/session');

                $data = array(
                    'error'         => $e->getMessage(),
                    'customerId'    => $customerSession->getCustomer()->getId(),
                    'storeId'       => Mage::app()->getStore()->getId(),
                    'POST'          => $_POST
                );

                Mage::log(print_r($data, true), null, 'order_history_errors.log', true);

                $this->_getSession()->addError(
                    Mage::helper('goodahead_orderedproducts')->__('Unable to add products to cart!')
                );
            }
        }

        $this->_redirectUrl($redirectUrl);

        return $this;
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
