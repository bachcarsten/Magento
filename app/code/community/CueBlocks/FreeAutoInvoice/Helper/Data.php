<?php
/**
 * Description of
 * @package   CueBlocks_
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Francesco Magazzu' <francesco.magazzu at cueblocks.com>
 * @support   <magento at cueblocks.com>
 */

class CueBlocks_FreeAutoInvoice_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_BASE_PATH = 'sales/auto_invoice';

    public function getConfig($storeId = null)
    {
        $config = new Varien_Object(
            Mage::getStoreConfig(self::CONFIG_BASE_PATH , $storeId
            )
        );
        return $config;
    }
}