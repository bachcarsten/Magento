<?php

 /**
 * WebShopApps Shipping Module
 *
 * @category    WebShopApps
 * @package     WebShopApps_$(PROJECT_NAME)
 * User         joshstewart
 * Date         08/07/2013
 * Time         12:00
 * @copyright   Copyright (c) $(YEAR) Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, $(YEAR), Zowta, LLC - US license
 * @license     http://www.WebShopApps.com/license/license.txt - Commercial license
 *
 */
require_once 'Mage/Checkout/controllers/OnepageController.php';


class Webshopapps_Wsafreightcommon_AjaxController extends Mage_Checkout_OnepageController
{
    /**public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }*/


    private $_rates;
    protected $_address;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;

    protected $_checkoutSession;


    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError() //|| $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    public function getFreightAction() {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isGet()) {
            $liftgateRequired = $this->getRequest()->getParam('liftgate_required') == "true" ? true : false;
            $notifyRequired = $this->getRequest()->getParam('notify_required') == "true" ? true : false;
            $insideRequired = $this->getRequest()->getParam('inside_required') == "true" ? true : false;
            $shiptoType = $this->getRequest()->getParam('shipto_type');
        } else {
            $liftgateRequired = false;
            $notifyRequired = false;
            $insideRequired = false;
            $shiptoType = 0;
        }

        $this->getOnepage()->getQuote()->getShippingAddress()->setLiftgateRequired($liftgateRequired);
        $this->getOnepage()->getQuote()->getShippingAddress()->setNotifyRequired($notifyRequired);
        $this->getOnepage()->getQuote()->getShippingAddress()->setNotifyRequired($insideRequired);
        $this->getOnepage()->getQuote()->getShippingAddress()->setShiptoType($shiptoType);

        $this->getAddress()->setCollectShippingRates(true);

        $this->getOnepage()->getQuote()->save();

        $this->getAddress()->collectShippingRates()->save();

        $result = $this->_getShippingMethodsHtml();

        $this->getResponse()->setBody($result);
    }

    protected function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getOnepage()->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }


    protected function getShippingPrice($price, $flag)
    {
        return $this->getOnepage()->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }


    /**
     * This should be in model really
     */
    protected function getShippingRates()
    {
        $address = $this->getAddress();

        if (empty($this->_rates)) {
            $address->collectShippingRates()->save();

            $groups = $address->getGroupedAllShippingRates();

            return $this->_rates = $groups;
        }

        return $this->_rates;
    }
}