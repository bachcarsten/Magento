<?php

class Goodahead_PrivateSales_Model_Observer
{
	public function checkAccess($observer)
	{
		if( Mage::app()->getStore()->isAdmin() ) {
			return $this;
		}
		
		$restrictionEnabled = (bool) Mage::getStoreConfig('goodahead/privatesales/enabled');
		$restrictionType = Mage::getStoreConfig('goodahead/privatesales/type');
		$restrictionStartupPage = Mage::getStoreConfig('goodahead/privatesales/startup_page');
		
		if( (bool) $restrictionEnabled != true ) {
			return $this;
		}
		
		$allowedActions = array_keys((array) Mage::getConfig()->getNode('frontend/goodahead/privatesales/actions/allowed_global'));
		if( $restrictionType == Goodahead_PrivateSales_Model_Privatesales::TYPE_LOGIN_REGISTER ) {
			$allowedRegisterActions = array_keys((array) Mage::getConfig()->getNode('frontend/goodahead/privatesales/actions/allowed_register'));
			$allowedActions = array_merge($allowedActions, $allowedRegisterActions);
		}

		/* @var $controller Mage_Core_Controller_Front_Action */
		$controller = $observer->getEvent()->getControllerAction();
		
        $redirectUrl = false;
        if( !Mage::helper('customer')->isLoggedIn() && !in_array($controller->getFullActionName(), $allowedActions) ) {
            if( $restrictionStartupPage == Goodahead_PrivateSales_Model_Privatesales::REDIRECT_LANDING_PAGE ) {
            	$allowedActions[] = 'cms_page_view';
            	
            	if( $controller->getRequest()->getParam('page_id') === $restrictionStartupPage ) {
            		$redirectUrl = false;
            		return $this;
            	}
            	
                if( (!in_array($controller->getFullActionName(), $allowedActions))) {
                	$page = Mage::getModel('cms/page')->load($restrictionStartupPage);
                    $redirectUrl = Mage::getUrl('', array('_direct' => $page->getIdentifier()));
                }
            } else {
            	$redirectUrl = Mage::getUrl('customer/account/login');
            }

            if( $redirectUrl ) {
                $controller->getResponse()->setRedirect($redirectUrl);
                $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            }

            Mage::getSingleton('core/session')->setWebsiteRestrictionAfterLoginUrl(Mage::getUrl());
        } elseif (Mage::getSingleton('core/session')->hasAfterLoginUrl()) {
			$response->setRedirect(Mage::getSingleton('core/session')->getAfterLoginUrl(true));
			$controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }

		return $this;
	}
	
    public function applyLayoutUpdates($observer)
    {
    	$layout = $observer->getEvent()->getLayout();
    	$types = array(Goodahead_PrivateSales_Model_Privatesales::TYPE_LOGIN_REGISTER, Goodahead_PrivateSales_Model_Privatesales::TYPE_LOGIN);
    	
        if( in_array( (int) Mage::getStoreConfig('goodahead/privatesales/type'), $types, true) ) {
            $layout->getUpdate()->addHandle('goodahead_privatesales_type');
        }
    }
    
    public function checkRegistrationEnabled($observer)
    {
        $result = $observer->getEvent()->getResult();
        if( !Mage::getStoreConfig('goodahead/privatesales/enabled') ) {
        	return $this;
        }
        
        $restrictionType = Mage::getStoreConfig('goodahead/privatesales/type');
        if( $restrictionType == Goodahead_PrivateSales_Model_Privatesales::TYPE_LOGIN_REGISTER ) {
            $result->setIsAllowed(true);
        } else {
        	$result->setIsAllowed(false);
        }
    }
    
    public function customerLogin($observer)
    {
    	$restrictionEnabled = (bool) Mage::getStoreConfig('goodahead/privatesales/enabled');
    	if( !$restrictionEnabled ) {
    		return $this;
    	}
    	
    	$customer = $observer->getEvent()->getCustomer();
    	$allowedGroups = explode(',', Mage::getStoreConfig('goodahead/privatesales/allowed_groups'));

    	$validationPassed = in_array($customer->getGroupId(), $allowedGroups); 
    	
    	if( $customer && $customer->getId() && $validationPassed ) {
    		return $this;
    	} else {
    		Mage::getSingleton('customer/session')->logout();
    		Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl());
    		Mage::app()->getFrontController()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
    	}
    	return $this;
    }
}