<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MagePsycho_Massimporterpro_Model_Import_Abstract extends Mage_Core_Model_Abstract
{
    protected $_importType = '';
    protected $_logPath = '';
    protected $_totalCount = 0;
    protected $_successCount = 0;
    protected $_errorCount = 0;
    protected $_skipCount = 0;
    protected $_results = array();

    abstract public function importData($importData, $options = array());

    public function getSuccessCount()
    {
        return (int)$this->_successCount;
    }

    public function getErrorCount()
    {
        return (int)$this->_errorCount;
    }

    public function getSkipCount()
    {
        return (int)$this->_skipCount;
    }

    public function getTotalCount()
    {
        return (int)$this->_totalCount;
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function getHelper()
    {
        return Mage::helper('magepsycho_massimporterpro');
    }

    public function log($data, $for = 'web')
    {
        $helper = $this->getHelper();
        if (!$helper->getConfig('enable_log')) {
            return;
        }
        $path = Mage::getBaseDir('var') . DS . 'log' . DS . 'magepsycho' . DS . 'massimporterpro' . DS . $this->_importType . DS . $for;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $file = 'magepsycho/massimporterpro/' . $this->_importType . '/' . $for . '/' . trim(date('Y-m-d H-i-s'), '/') . '.log';
        Mage::log($data, null, $file, true);
    }

    public function getConnection($type = 'core_read')
    {
        return Mage::getSingleton('core/resource')->getConnection($type);
    }

    public function getTableName($tableName)
    {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    public function getEntityTable($attributeId)
    {
        $connection   = $this->getConnection('core_read');
        $sql          = "SELECT backend_type
					FROM " . $this->getTableName('eav_attribute') . "
				WHERE
					entity_type_id = ?
					AND attribute_id = ?";
        $entityTypeId = $this->getEntityTypeId();
        $backendType  = $connection->fetchOne($sql, array($entityTypeId, $attributeId));
        $tableName    = '';
        if (!empty($backendType)) {
            $tableName = 'catalog_product_entity';
            if ($backendType != 'static') {
                $tableName .= '_' . $backendType;
            }
        }
        return $tableName;
    }

    public function getAttributeId($attributeCode = 'price')
    {
        $connection   = $this->getConnection('core_read');
        $sql          = "SELECT attribute_id
					FROM " . $this->getTableName('eav_attribute') . "
				WHERE
					entity_type_id = ?
					AND attribute_code = ?";
        $entityTypeId = $this->getEntityTypeId();
        return $connection->fetchOne($sql, array($entityTypeId, $attributeCode));
    }

    public function getEntityTypeId($entityTypeCode = 'catalog_product')
    {
        $connection = $this->getConnection('core_read');
        $sql        = "SELECT entity_type_id FROM " . $this->getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
        return $connection->fetchOne($sql, array($entityTypeCode));
    }

    public function getIdFromSku($sku)
    {
        $connection = $this->getConnection('core_read');
        $sql        = "SELECT entity_id FROM " . $this->getTableName('catalog_product_entity') . " WHERE sku = ?";
        return $connection->fetchOne($sql, array($sku));
    }

    public function getSkuFromId($entity_id)
    {
        $connection = $this->getConnection('core_read');
        $sql        = "SELECT sku FROM " . $this->getTableName('catalog_product_entity') . " WHERE entity_id = ?";
        return $connection->fetchOne($sql, array($entity_id));
    }

    public function checkIfSkuExists($sku)
    {
        $connection = $this->getConnection('core_read');
        $sql        = "SELECT COUNT(*) AS count_no FROM " . $this->getTableName('catalog_product_entity') . "	WHERE sku = ?";
        $count      = $connection->fetchOne($sql, array($sku));
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkIfWebsiteExists($websiteId)
    {
        $connection = $this->getConnection('core_read');
        $sql        = "SELECT COUNT(*) AS count_no FROM " . $this->getTableName('core_website') . "	WHERE website_id = ?";
        $count      = $connection->fetchOne($sql, array($websiteId));
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkIfRowExists($field, $productId, $storeId)
    {
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);
        $connection  = $this->getConnection('core_read');
        $sql         = "SELECT COUNT(*) AS count_no FROM " . $this->getTableName($tableName) . "	WHERE entity_id = ? AND attribute_id = ? AND store_id = ?";
        $count       = $connection->fetchOne($sql, array($productId, $attributeId, $storeId));
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getPrice($field, $productId, $storeId)
    {
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);
        $connection  = $this->getConnection('core_read');
        $sql         = "SELECT `value` FROM " . $this->getTableName($tableName) . "	WHERE entity_id = ? AND attribute_id = ? AND store_id = ?";
        return $connection->fetchOne($sql, array($productId, $attributeId, $storeId));
    }

    public function insertRow($field, $value, $productId, $storeId, $additonalData = array())
    {
        $connection  = $this->getConnection('core_write');
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);

        $sql = "INSERT INTO " . $this->getTableName($tableName) . "
					SET
				entity_type_id = ?,
				attribute_id = ?,
				store_id = ?,
				entity_id = ?,
				value = ?
				";
        $connection->query($sql, array($this->getEntityTypeId(), $attributeId, $storeId, $productId, $value));
    }

    public function updateRow($field, $value, $productId, $storeId, $additonalData = array())
    {
        $connection  = $this->getConnection('core_write');
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);
        $sql         = "UPDATE " . $this->getTableName($tableName) . "
					SET  `value` = ?
				WHERE  attribute_id = ?
				AND entity_id = ?
				AND store_id = ?";
        $connection->query($sql, array($value, $attributeId, $productId, $storeId));
    }

    public function removeSpecialPriceDates($field, $productId, $storeIds)
    {
        $connection  = $this->getConnection('core_write');
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);
        $storeId     = isset($storeIds[0]) ? $storeIds[0] : Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        $where   = array();
        $where[] = $connection->quoteInto('attribute_id = ?', $attributeId);
        $where[] = $connection->quoteInto('entity_id = ?', $productId);
        if (count($storeIds) > 1) {
            $where[] = $connection->quoteInto('store_id IN (?)', $storeIds);
        } else {
            $where[] = $connection->quoteInto('store_id = ?', $storeId);
        }

        $sql = "UPDATE " . $this->getTableName($tableName) . "
				SET  `value` = NULL
				WHERE " . join(' AND ', $where);
        $connection->query($sql);
    }

    public function updateDecimalPrices($field, $value, $productId, $storeIds, $options = array())
    {
        $helper      = $this->getHelper();
        $connection  = $this->getConnection('core_write');
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);
        $storeId     = isset($storeIds[0]) ? $storeIds[0] : Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        $helper->log(__METHOD__, true);
        $removeValue = false;

        //values marked with x are to be removed
        if (in_array($value, array('x', 'X'))) {
            $removeValue = true;
        }

        //process only if fields are of decimal types
        if (!$removeValue && !in_array($field, array('special_from_date', 'special_to_date'))) {
            //if +/- signs or % are present, get related field expression
            $value = $this->_getValueExpression($value, $field, $productId, $storeId);
        }

        $where   = array();
        $where[] = $connection->quoteInto('attribute_id = ?', $attributeId);
        $where[] = $connection->quoteInto('entity_id = ?', $productId);
        if (count($storeIds) > 1) {
            $where[] = $connection->quoteInto('store_id IN (?)', $storeIds);
        } else {
            $where[] = $connection->quoteInto('store_id = ?', $storeId);
        }

        $sql = "UPDATE " . $this->getTableName($tableName) . " SET ";
        if (in_array($field, array('special_from_date', 'special_to_date'))) {
            if ($removeValue) {
                $sql .= "`value` = NULL";
            } else {
                $sql .= $connection->quoteInto('value = ?', $value);
            }
        } else {
            if ($removeValue) {
                $sql .= "`value` = NULL";
            } else {
                $value = $this->_getRoundedValue($value, $options);
                $sql .= "`value` = $value";
            }
        }

        $sql .= " WHERE " . join(' AND ', $where);
        $helper->log('SQL::' . $sql);
        $connection->query($sql);
    }

    public function copyDefaultStorePrice($field, $productId, $storeIds)
    {
        $defaultStoreId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        if (count($storeIds) == 1 && $storeIds[0] == $defaultStoreId) {
            return;
        }

        $connection  = $this->getConnection('core_write');
        $attributeId = $this->getAttributeId($field);
        $tableName   = $this->getEntityTable($attributeId);

        if (count($storeIds)) {
            foreach ($storeIds as $_storeId) {
                // Copy price value from global scope if store/website level price value doesn't exist
                $sql = "INSERT IGNORE INTO $tableName (`entity_type_id`, `attribute_id`, `store_id`, `entity_id`, `value`) "
                    . " SELECT t.`entity_type_id`, t.`attribute_id`, $_storeId, t.`entity_id`, t.`value` FROM $tableName AS t"
                    . " WHERE t.entity_id = ? AND t.attribute_id = ? AND t.store_id = ?";
                $connection->query($sql, array($productId, $attributeId, $defaultStoreId));
            }
        }
    }

    public function isMultiWebsite()
    {
        $websites = Mage::app()->getWebsites();
        if (count($websites) > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function isSingleStore()
    {
        return Mage::app()->isSingleStoreMode();
    }

    public function getWebsiteStores($websiteId)
    {
        $storeIds = Mage::getModel('core/website')->load($websiteId)->getStoreIds();
        return array_values($storeIds);
    }

    public function getAllStoreIds($websiteId)
    {
        $storeIds = array();
        if ($websiteId == 0) {
            $storeIds[] = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        } else {
            $storeIds = $this->getWebsiteStores($websiteId);
        }
        return $storeIds;
    }

    public function isPriceScopeGlobal()
    {
        return !Mage::getStoreConfig('catalog/price/scope'); #0: global, 1: website
    }

    protected function _getValueExpression($value, $field, $productId, $storeId)
    {
        $helper  = $this->getHelper();
        $sign    = substr($value, 0, 1);
        $percent = ('%' == substr($value, -1, 1));

        //Relative Pricing (+/- of fixed or percentage)
        if (in_array($sign, array('+', '-'))) {
            $value = substr($value, 1);
            if ($percent) {
                $value = substr($value, 0, -1);
            }
            $value = floatval($value);

            $value = $percent ? '`value` * ' . $value . '/100' : $value;
            $value = '`value`' . $sign . $value;
        }
        if (in_array($field, array('special_price', 'tier_price', 'group_price')) && $percent && !in_array($sign, array('+', '-'))) { // price based (if no +/-)

            $price = $this->getPrice('price', $productId, $storeId);
            if ($price > 0) {
                $pc    = substr($value, 0, -1);
                $value = ($pc / 100) * $price;
            }
        }
        $helper->log('_getValueExpression()::' . $value);
        return $value;
    }

    protected function _getRoundedValue($value, $options = array())
    {
        $helper       = $this->getHelper();
        $roundingType = isset($options['price_rounding']) ? $options['price_rounding'] : MagePsycho_Massimporterpro_Model_System_Config_Source_Rounding::ROUNDING_NO;
        if ($roundingType == MagePsycho_Massimporterpro_Model_System_Config_Source_Rounding::ROUNDING_NORMAL) {
            $value = 'ROUND(' . $value . ')';
        } else if ($roundingType == MagePsycho_Massimporterpro_Model_System_Config_Source_Rounding::ROUNDING_TO_NEAREST) {
            $roundingNearest = (float)isset($options['rounding_nearest']) ? $options['rounding_nearest'] : 0.00;
            $value           = 'FLOOR(' . $value . ') + ' . $roundingNearest;
        }
        #$helper->log('$roundingType::' . $roundingType);
        return $value;
    }
}