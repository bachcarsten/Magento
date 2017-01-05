<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Mysql4/Core/Website/Collection.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ ZjUIEqhiciPscajj('84c4c66e5d30c8e2f70856f1da967871'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitquantitymanager_Model_Mysql4_Core_Website_Collection extends Mage_Core_Model_Mysql4_Website_Collection
{
    public function load($printQuery = false, $logQuery = false)
    {
// start aitoc code 
       
        $this->getSelect()->where('main_table.code != "aitoccode" ');

// finish aitoc code

        parent::load($printQuery, $logQuery);
        return $this;
    }

} } 