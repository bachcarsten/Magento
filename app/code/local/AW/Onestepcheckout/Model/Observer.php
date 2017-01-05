<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onestepcheckout
 * @version    1.0.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onestepcheckout_Model_Observer
{
    public function controllerActionPredispatchCheckout($observer)
    {
        $controllerInstance = $observer->getControllerAction();
        //for compatibility with AW_Mobile
        if (
            Mage::helper('core')->isModuleEnabled('AW_Mobile') &&
            !Mage::helper('awmobile')->getDisabledOutput() &&
            (
                Mage::getSingleton('customer/session')->getShowDesktop() === FALSE ||
                Mage::helper('awmobile')->getTargetPlatform() == AW_Mobile_Model_Observer::TARGET_MOBILE
            )
        ) {
            //no redirect if in mobile theme
            return;
        }
        if (
            $controllerInstance instanceof Mage_Checkout_OnepageController &&
            $controllerInstance->getRequest()->getActionName() !== 'success' &&
            $controllerInstance->getRequest()->getActionName() !== 'failure' &&
            Mage::helper('aw_onestepcheckout/config')->isEnabled()
        ) {
            $controllerInstance->getResponse()->setRedirect(
                Mage::getUrl('onestepcheckout/index', array('_secure'=>true))
            );
            $controllerInstance->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * @param $observer
     * submit order after
     */
    public function checkoutSubmitAllAfter($observer)
    {
        $oscOrderData = Mage::getSingleton('checkout/session')->getData('aw_onestepcheckout_order_data');
        if (!is_array($oscOrderData)) {
            $oscOrderData = array();
        }
        // add customer comment
        if (!Mage::helper('aw_onestepcheckout/ddan')->isDDANEnabled()) {
            if (array_key_exists('comments', $oscOrderData)) {
                $comment = $oscOrderData['comments'];
                if ($lastOrderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId()) {
                    $order = Mage::getModel('sales/order')->load($lastOrderId);
                    $order
                        ->addStatusHistoryComment(Mage::helper('aw_onestepcheckout')->__('Comment by customer: %s', $comment))
                        ->setIsVisibleOnFront(true)
                        ->save()
                    ;
                }
            }
        }

        // subscribe to newsletter
        if (array_key_exists('is_subscribed', $oscOrderData) && $oscOrderData['is_subscribed']) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getId()) {
                $data = array(
                    'email'       => $customer->getEmail(),
                    'first_name'  => $customer->getFirstname(),
                    'last_name'   => $customer->getLastname(),
                    'customer_id' => $customer->getId(),
                );
            } else {
                $billing = $oscOrderData['billing'];
                $data = array(
                    'email'      => $billing['email'],
                    'first_name' => $billing['firstname'],
                    'last_name'  => $billing['lastname'],
                );
            }
            if (array_key_exists('segments_select', $oscOrderData)) {
                $data['segments_codes'] = $oscOrderData['segments_select'];
            }
            $data['store_id'] = Mage::app()->getStore()->getId();
            Mage::helper('aw_onestepcheckout/newsletter')->subscribeCustomer($data);
        }

        //clear saved values
        Mage::getSingleton('checkout/session')->setData('aw_onestepcheckout_form_values', array());
        Mage::getSingleton('checkout/session')->setData('aw_onestepcheckout_order_data', array());
    }
}