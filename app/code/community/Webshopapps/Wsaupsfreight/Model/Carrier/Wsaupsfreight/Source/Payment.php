<?php
/* YRC Freight Shipping
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsaupsfreight
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

class Webshopapps_Wsaupsfreight_Model_Carrier_Wsaupsfreight_Source_Payment {
	
public function toOptionArray()
    {
        $wsaupsfreight = Mage::getSingleton('wsaupsfreight/carrier_wsaupsfreight');
        $arr = array();
        foreach ($wsaupsfreight->getCode('payment') as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>Mage::helper('usa')->__($v));
        }
        return $arr;
    }
}
