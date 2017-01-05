<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action
{
    protected function _getCsv($priceType, $productIds = array(), $storeId = 0)
    {
        $helper     = Mage::helper('magepsycho_massimporterpro');
        $websiteId  = Mage::app()->getStore($storeId)->getWebsiteId();
        $csv        = '';
        $_columns   = array();
        $_columns[] = "sku";

        $_columns[] = "website_id";
        if (in_array($priceType, array(0, 1, 3))) {
            $_columns[] = "cost";
            $_columns[] = "price";
            if ($helper->checkVersion('1.6')) {
                $_columns[] = "msrp";
            }
        }
        if (in_array($priceType, array(0, 2, 3))) {
            $_columns[] = "special_price";
            $_columns[] = "special_from_date";
            $_columns[] = "special_to_date";
        }

        $customerGroups = $helper->getCustomerGroups();
        if (in_array($priceType, array(0, 4, 6))) {
            $_columns[] = "tier_price:_all_";
            foreach ($customerGroups as $customerGroup) {
                $_columns[] = "tier_price:" . $customerGroup['label'];
            }
        }

        if ($helper->checkVersion('1.7')) {
            if (in_array($priceType, array(0, 5, 6))) {
                foreach ($customerGroups as $customerGroup) {
                    $_columns[] = "group_price:" . $customerGroup['label'];
                }
            }
        }

        $data = array();
        foreach ($_columns as $column) {
            $data[] = '"' . $column . '"';
        }
        $csv .= implode(',', $data) . "\n";

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('entity_id', array('in' => $productIds));

        foreach ($collection as $item) {
            $_product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getId());

            $data   = array();
            $data[] = '"' . $_product->getSku() . '"'; #sku
            $data[] = $websiteId;
            if (in_array($priceType, array(0, 1, 3))) {
                $data[] = '"' . sprintf("%.2f", $_product->getCost()) . '"';
                $data[] = '"' . sprintf("%.2f", $_product->getPrice()) . '"';
                if ($helper->checkVersion('1.6')) {
                    $msrp = $_product->getMsrp();
                    if ($msrp > 0) {
                        $msrp = sprintf("%.2f", $_product->getMsrp());
                    }
                    $data[] = '"' . $msrp . '"';
                }
            }
            if (in_array($priceType, array(0, 2, 3))) {
                $specialPrice = $_product->getSpecialPrice();
                if ($specialPrice > 0) {
                    $specialPrice = sprintf("%.2f", $_product->getSpecialPrice());
                }
                $data[] = '"' . $specialPrice . '"';
                $data[] = '"' . $_product->getSpecialFromDate() . '"';
                $data[] = '"' . $_product->getSpecialToDate() . '"';
            }
            #TIER PRICES
            if (in_array($priceType, array(0, 4, 6))) {
                $tierPrices   = $_product->getData('tier_price');
                $tierPriceAll = array();
                if (!empty($tierPrices)) {
                    foreach ($tierPrices as $_tierPriceAll) {
                        if ($_tierPriceAll['all_groups'] == 1/* && $_tierPriceAll['website_id'] == 0*/) { //@FIXME
                            $tierPriceAll[] = intval($_tierPriceAll['price_qty']) . ':' . sprintf("%.2f", $_tierPriceAll['price']); //@FIXME $qty = floatval($qty); if getIsQtyDecimal
                        }
                    }
                }
                $data[] = '"' . implode(';', $tierPriceAll) . '"'; #tier_price:_all_

                foreach ($customerGroups as $customerGroup) {

                    $tierPrice = array();
                    if (!empty($tierPrices)) {
                        foreach ($tierPrices as $_tierPrice) {
                            if ($_tierPrice['cust_group'] == $customerGroup['value']/* && $_tierPrice['website_id'] == 0*/) { //@FIXME
                                $tierPrice[] = intval($_tierPrice['price_qty']) . ':' . sprintf("%.2f", $_tierPrice['price']);
                            }
                        }
                    }
                    $data[] = '"' . implode(';', $tierPrice) . '"';
                }
            }

            #GROUP PRICES
            if ($helper->checkVersion('1.7')) {
                if (in_array($priceType, array(0, 5, 6))) {
                    $groupPrices = $_product->getData('group_price');
                    foreach ($customerGroups as $customerGroup) {

                        $groupPrice = '';
                        if (!empty($groupPrices)) {
                            foreach ($groupPrices as $_groupPrice) {
                                if ($_groupPrice['cust_group'] == $customerGroup['value']/* && $_groupPrice['website_id'] == 0*/) { //@FIXME
                                    $groupPrice = sprintf("%.2f", $_groupPrice['price']);
                                }
                            }
                        }
                        $data[] = '"' . $groupPrice . '"';
                    }
                }
            }

            $csv .= implode(',', $data) . "\n";

        }
        return $csv;
    }

    public function massExportPricesAction()
    {
        $helper     = Mage::helper('magepsycho_massimporterpro');
        $productIds = (array)$this->getRequest()->getParam('product');
        $priceType  = $this->getRequest()->getParam('price_type');
        $storeId    = $this->getRequest()->getParam('store');
        switch ($priceType) {
            case 0:
                $filePrefix = 'all-';
                break;
            case 1:
                $filePrefix = 'regular-';
                break;
            case 2:
                $filePrefix = 'special-';
                break;
            case 3:
                $filePrefix = 'regular-special-';
                break;
            case 4:
                $filePrefix = 'tier-';
                break;
            case 5:
                $filePrefix = 'group-';
                break;
            case 6:
                $filePrefix = 'tier-group-';
                break;

            default:
                $filePrefix = 'all-';
                break;
        }

        $fileName = $filePrefix . 'prices-' . date('Y-m-d-His') . '.csv';
        $content  = $this->_getCsv($priceType, $productIds, $storeId);
        $this->_prepareDownloadResponse($fileName, $content);
    }
}