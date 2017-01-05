<?php
class Goodahead_OrderBySku_Model_Cart
{
    /**
     * Contains sku - qty pairs to be added to cart
     * @var array
     */
    protected $_items = array();

    /**
     * Parse csv file and add items
     *
     * @param string $fileId
     * @return array
     */
    public function prepareItemsFromFile($fileId)
    {
        $uploader = new Varien_File_Uploader($fileId);
        $uploader->setAllowedExtensions(array('csv'));

        $destinationFolder = Mage::getBaseDir('media') . DS . 'import' . DS . 'orderbysku';
        Mage::app()->getConfig()->createDirIfNotExists($destinationFolder);
        $uploader->save($destinationFolder);

        $file = $destinationFolder . DS . $uploader->getUploadedFileName();
        $handle = fopen($file, 'r+');
        while ($product = fgetcsv($handle)) {
            if ($product[0] == 'sku' && $product[1] == 'qty') {
                continue;
            }
            if (count($product) == 2 && is_numeric($product[1])) {
                $this->_items[] = array('sku' => $product[0], 'qty' => $product[1]);
            } else {
                Mage::throwException($this->_getHelper()->__('Invalid file format'));
            }
        }
        return $this->_items;
    }

    /**
     * Add products from array
     *
     * @param array $products
     */
    public function prepareItemsFromArray($products)
    {
        foreach ($products as $index => $product) {
            if (empty($product['sku'])) {
                unset ($products[$index]);
                continue;
            }
            $this->_items[] = $product;
        }
    }
    /**
     * Add products to shopping cart and
     * return number of successfully added ones
     * @return int
     */
    public function process()
    {
        $cart   = $this->_getCart();
        $itemsAdded = 0;
        foreach ($this->_items as $_item) {
            $productId = Mage::getModel('catalog/product')->getIdBySku($_item['sku']);
            if (!$productId) {
                $this->_getSession()->addError($this->_getHelper()->__('Product with SKU: \'%s\' was not found', $_item['sku']));
                continue;
            }
            $buyRequest = array('qty' => $_item['qty']);
            $cart->addProduct($productId, $buyRequest);
            $itemsAdded += $_item['qty'] ? $_item['qty'] : 1;
            $this->_items[] = $_item;
        }
        $cart->save();

        return $itemsAdded;
    }

    /**
     * Get cart singleton
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session mode3l instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get helper instance
     *
     * @return Goodahead_OrderBySku_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('goodahead_orderbysku');
    }

    /**
     * Get added items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return count($this->_items);
    }
}