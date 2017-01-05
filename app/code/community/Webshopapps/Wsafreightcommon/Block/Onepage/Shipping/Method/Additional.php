<?php

 /**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_$(PROJECT_NAME)
 * User         joshstewart
 * Date         05/07/2013
 * Time         12:14
 * @copyright   Copyright (c) $(YEAR) Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, $(YEAR), Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */

class Webshopapps_Wsafreightcommon_Block_Onepage_Shipping_Method_Additional extends Mage_Checkout_Block_Onepage_Shipping_Method_Additional
{
    public function getLiftgateRequired()
    {
        if(Mage::getStoreConfig('shipping/wsafreightcommon/default_liftgate',Mage::app()->getStore())
            && $this->getQuote()->getShippingAddress()->getLiftgateRequired() == ''){
            return true;
        } else {
            return $this->getQuote()->getShippingAddress()->getLiftgateRequired();
        }
    }

    public function getNotifyRequired()
    {
        return $this->getQuote()->getShippingAddress()->getNotifyRequired();
    }

    public function getInsideDelivery()
    {
        return $this->getQuote()->getShippingAddress()->getInsideDelivery();
    }

    public function getShiptoType()
    {
        return $this->getQuote()->getShippingAddress()->getShiptoType();
    }

    public function getShiptoTypeHtmlSelect($defValue=null) {

        if (is_null($defValue)) {
            $defValue=Mage::getStoreConfig('shipping/wsafreightcommon/default_address_type');
        }

        $options = Mage::helper('wsafreightcommon')->getOptions();

        $html = $this->getLayout()->createBlock('core/html_select')
            ->setName('shipto_type')
            ->setTitle(Mage::helper('wsafreightcommon')->__('Address Type'))
            ->setId('shipto_type')
            ->setClass('required-entry')
            ->setValue($defValue)
            ->setOptions($options)
            ->setExtraParams("onchange=\"liftgateListener()\"")
            ->getHtml();
        return $html;

    }

    public function dontShowCommonFreight() {
        return Mage::helper('wsafreightcommon')->dontShowCommonFreight(
            $this->getQuote()->getShippingAddress()->getAllItems(),
            $this->getQuote()->getShippingAddress()->getWeight());
    }
}