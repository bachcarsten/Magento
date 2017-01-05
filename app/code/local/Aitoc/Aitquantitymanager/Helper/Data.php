<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Helper/Data.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ DeIckpqwCwEsCZee('2e9e906249a3cfe48527ce949b17091b'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitquantitymanager_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getHiddenWebsiteId()
    {
        $oModel = Mage::getModel('aitquantitymanager/mysql4_core_website');
        
        $iWebsiteId = $oModel->getIdByCode('aitoccode');
        
        return $iWebsiteId; 
    }
    
    public function getCataloginventoryStockTable()
    {
        $sOriginalAitocTable = 'aitoc_cataloginventory_stock_item'; 
        $sOriginalStockTable = 'cataloginventory_stock'; 
        
        $sCurrentAitocTable = Mage::getSingleton('core/resource')->getTableName('aitquantitymanager/stock_item');
        
        if ($sOriginalAitocTable == $sCurrentAitocTable)
        {
            $sCurrentStockTable = $sOriginalStockTable;
        }
        else 
        {
            $sPrefix = substr($sCurrentAitocTable, 0, strpos($sCurrentAitocTable, $sOriginalAitocTable));
            $sCurrentStockTable = $sPrefix . $sOriginalStockTable;
        }
        
        return $sCurrentStockTable; 
    }
    
    
}

 } ?>