<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Rewrite/FrontCatalogProductIndexerPrice.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ WyuftqhcNclsNOEE('6b59625dab2cf1c65fa62dd0dcdb9fb0'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitquantitymanager_Model_Rewrite_FrontCatalogProductIndexerPrice extends Mage_Catalog_Model_Product_Indexer_Price
{
    // overide parent
    protected function _construct()
    {
//        $this->_init('catalog/product_indexer_price');
        $this->_init('aitquantitymanager/frontCatalogResourceEavMysql4ProductIndexerPrice');
    }
} } 