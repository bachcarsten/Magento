<?php
class Goodahead_OrderBySku_AccountController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');

        if ($block = $this->getLayout()->getBlock('order_by_sku')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('Order by SKU'));
        $this->renderLayout();
    }

    public function addAction()
    {
        /** @var Goodahead_OrderBySku_Model_Cart $cart  */
        $cart = Mage::getModel('goodahead_orderbysku/cart');

        try {
            if (isset($_FILES['sku_file'])
                && isset($_FILES['sku_file']['tmp_name'])
                && $_FILES['sku_file']['tmp_name']
            ) {
                $cart->prepareItemsFromFile('sku_file');
            }

            $products = $this->getRequest()->getPost('products');

            $cart->prepareItemsFromArray($products);

            if ($cart->getItemsCount()) {
                $itemsAdded = $cart->process();
                $this->_getSession()->addSuccess($this->__('%s items were added to your cart', $itemsAdded));
            } else {
                $this->_getSession()->addError($this->__('Empty request'));
                $this->_redirectReferer();
                return;
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
        }

        $this->_redirectReferer();
    }

//    public function downloadAction()
//    {
//        $filename = 'sample.csv';
//        $content = Mage::helper('goodahead_orderbysku')->getSampleFileContent();
//        $this->_prepareDownloadResponse($filename, $content);
//    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}