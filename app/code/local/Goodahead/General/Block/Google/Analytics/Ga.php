<?php
class Goodahead_General_Block_Google_Analytics_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    protected $_cachedCategories = array();

    /**
     * Render information about specified orders and their items
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
        /* @var $order Mage_Sales_Model_Order */
        /* @var $item Mage_Sales_Model_Order_Item */

        $categoryResource = Mage::getSingleton('catalog/category')->getResource();

        foreach ($collection as $order) {

            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseGrandTotal(),
                $order->getBaseTaxAmount(),
                $order->getBaseShippingAmount(),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCity())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getRegion())),
                $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($address->getCountry()))
            );

            /* @var $orderItemCollection Mage_Sales_Model_Resource_Order_Item_Collection */
            $orderItemCollection = Mage::getResourceModel('sales/order_item_collection')->setOrderFilter($order);

            $orderItemCollection->getSelect()
                ->columns(array(
                    'category_path' => 'cc.path'
                ))
                ->joinLeft(
                    array('cc' => $categoryResource->getTable('catalog/category')),
                    'main_table.category_id = cc.entity_id',
                    array()
                )
                ->where('main_table.parent_item_id IS NULL')
                ->group('main_table.product_id');

            foreach ($orderItemCollection as $item) {
                if (!$item->isDeleted() && !$item->getParentItemId()) {
                    $result[] = sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                        $order->getIncrementId(),
                        $this->jsQuoteEscape($item->getSku()),
                        $this->jsQuoteEscape($item->getName()),
                        $this->jsQuoteEscape(
                            Mage::helper('core')->escapeHtml(
                                $this->_getPathCategoryNames($item->getCategoryPath())
                            )
                        ),
                        $item->getBasePrice(),
                        $item->getQtyOrdered()
                    );
                }
            }
            $result[] = "_gaq.push(['_trackTrans']);";
        }
        return implode("\n", $result);
    }

    protected function _getPathCategoryNames($categoryPath)
    {
        if ($categoryPath = trim($categoryPath)) {
            $categoryIds = explode('/', $categoryPath);

            if (is_array($categoryIds) && count($categoryIds) > 1) {
                $_requestCatIds = array();

                foreach($categoryIds as $catId) {
                    if (!isset($this->_cachedCategories[$catId])) {
                        $_requestCatIds[] = $catId;
                    }
                }

                if (count($_requestCatIds)) {
                    $storeId = Mage::app()->getStore()->getId();

                    /* @var $categoryCollection Mage_Catalog_Model_Resource_Category_Collection */
                    $categoryCollection = Mage::getModel('catalog/category')->getCollection();

                    /* @var $categoryNameAttribute Mage_Catalog_Model_Resource_Eav_Attribute */
                    $categoryNameAttribute = $categoryCollection->getResource()->getAttribute('name');

                    $categoryCollection->getSelect()
                        ->reset(Varien_Db_Select::COLUMNS)
                        ->columns(array(
                            'entity_id',
                            'category_name' => 'cev.value'
                        ))
                        ->join(
                            array('cev' => $categoryCollection->getResource()->getTable('catalog_category_entity_varchar')),
                            sprintf('e.entity_id = cev.entity_id AND cev.attribute_id = %d AND cev.entity_type_id = %d AND ((cev.store_id = %d) OR (cev.store_id = 0))',
                                $categoryNameAttribute->getAttributeId(),
                                $categoryNameAttribute->getEntityTypeId(),
                                $storeId
                            ),
                            array()
                        )
                        ->where('e.entity_id IN (?)', $_requestCatIds)
                        ->group('e.entity_id');

                    foreach($categoryCollection as $category) {
                        $this->_cachedCategories[$category->getEntityId()] = $category->getCategoryName();
                    }
                }

                $result = array();

                foreach($categoryIds as $catId) {
                    if (isset($this->_cachedCategories[$catId])) {
                        $result[] = $this->_cachedCategories[$catId];
                    } else {
                        $result[] = $catId;
                    }
                }
                return implode(' - ', $result);
            }
        }
        return null;
    }
}