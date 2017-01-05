<?php

require_once Mage::getModuleDir('controllers', 'Mage_Checkout').DS.'CartController.php';

class Mango_Loworderfee_CartController extends Mage_Checkout_CartController {

    public function preDispatch() {
        parent::preDispatch();
        $this->getRequest()->setRouteName('checkout');
        return $this;
    }

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            $minOrderActive = Mage::getStoreConfigFlag('sales/minimum_order/active');
            $lowOrderFeeActive = Mage::getStoreConfigFlag('sales/minimum_order/low_order_fee_active');

            /* added first order validation */
            if($minOrderActive){
                $_min_order_validation = true;
                if($lowOrderFeeActive){
                    $_min_order_validation = $this->_getQuote()->checkMinimumAmount();
                }else{
                    $_min_order_validation = $this->_getQuote()->validateMinimumAmount();
                }
                if(!$_min_order_validation){
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                    $cart->getCheckoutSession()->addNotice($warning);
                }
            }
            /* eof added first order validation */
        }

        /*foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $cart->getCheckoutSession()->addMessage($message);
            }
        }*/
        
        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
}
