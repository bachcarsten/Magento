<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Mysql4/Core/Website.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ jBcCrophhhkshDBB('ffc61c7c1bb96871dc26cb8e17ebfcdc'); ?><?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitquantitymanager_Model_Mysql4_Core_Website extends Mage_Core_Model_Mysql4_Website
{
// start aitoc code    
    public function getIdByCode($sCode)
    {
        if (!$sCode) return false;
        
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('website_id'))
            ->where('code=?', $sCode);

        return $this->_getReadAdapter()->fetchOne($select);
    }
// finish aitoc code    
    
} } 