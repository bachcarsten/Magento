<?php

/**
 * ShipSync
 *
 * @category   IllApps
 * @package    IllApps_Shipsync
 * @author     David Kirby (d@kernelhack.com)
 * @copyright  Copyright (c) 2010 EcoMATICS, Inc. DBA IllApps (http://www.illapps.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Shipment view
 */
class IllApps_Shipsync_Block_Adminhtml_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{

    /**
     * Construct
     */
    public function __construct()
    {
	/** Call parent */
    	parent::__construct();

        $i = 1;

	/** Loop through available packages */
        while ($package = Mage::getModel('shipping/shipment_package')->load($i)->getPackageId())
        {
            /** Get label URL */
	    $url = $this->getUrl('shipsync/index/label/', array('id' => Mage::getModel('shipping/shipment_package')->load($i)->getPackageId()));

            /** If package is available */
	    if (Mage::getModel('shipping/shipment_package')->load($i)->getOrderShipmentId() == $this->getShipment()->getId())
            {
		/** Add print label button */
                $this->_addButton('reprint_label_' . $package, array(
                    'label'     => Mage::helper('sales')->__('Print Label'),
                    'onclick'   => 'setLocation(\'' . $url . '\')',
                ));
            }
	    
            $i++;
        }
    }
}