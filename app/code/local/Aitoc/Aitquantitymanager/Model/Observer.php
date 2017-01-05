<?php
/**
 * Product:     Multi-Location Inventory
 * Package:     Aitoc_Aitquantitymanager_2.1.3_2.0.1_314694
 * Purchase ID: nILwa7zsqWm4gpHcLdhf7zqePRi3Axk569Gj6pIMvu
 * Generated:   2012-10-24 15:10:42
 * File path:   app/code/local/Aitoc/Aitquantitymanager/Model/Observer.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitquantitymanager')){ WyuftqhcNclsNOEE('29b58657da9dfe64e659df07d944d579'); ?><?php
/**
 * @copyright  Copyright (c) 2011 AITOC, Inc.
 */
if (version_compare( Mage::getVersion(), '1.4.0.0', 'ge') && version_compare( Mage::getVersion(), '1.4.1.0', 'lt'))
{
    class Aitoc_Aitquantitymanager_Model_Observer
    {
        public function onAdminhtmlSalesOrderCreditmemoRegisterBefore($observer)
        {            
        }
    }
}
elseif (version_compare(Mage::getVersion(), '1.4.1.0', 'ge'))
{
    class Aitoc_Aitquantitymanager_Model_Observer
    {
        public function onAdminhtmlSalesOrderCreditmemoRegisterBefore($observer)
        {
            $creditmemo = $observer->getCreditmemo();
            foreach ($creditmemo->getAllItems() as $creditmemoItem)
            {
                if ($creditmemoItem->getStoreId() && Mage::registry('aitoc_order_refund_store_id') === NULL)
                {
                    Mage::register('aitoc_order_refund_store_id', $creditmemoItem->getStoreId());
                    return true;
                }
            }
        }
    }
} } 