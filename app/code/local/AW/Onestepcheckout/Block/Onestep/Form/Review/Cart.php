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


class AW_Onestepcheckout_Block_Onestep_Form_Review_Cart extends Mage_Checkout_Block_Onepage_Review_Info
{
    protected $_timerConfig = array(
        'block_html_id'                 => 'aw-onestepcheckout-review-cart-redirect-timer-block',
        'timer_clock_html_id'           => 'aw-onestepcheckout-review-cart-redirect-timer-clock',
        'redirect_now_action_html_id'   => 'aw-onestepcheckout-review-cart-redirect-timer-action-redirect',
        'cancel_action_html_id'         => 'aw-onestepcheckout-review-cart-redirect-timer-action-cancel',
        'title_text'                    => "You will be redirected to another page in %s second(s).",
        'description_text'              => "You can lose your order progress.",
        'redirect_now_action_text'      => "Redirect Now",
        'cancel_action_text'            => "Cancel",
    );

    public function getHelperTimerBlockHtml()
    {
        $block = $this->getLayout()->createBlock(
            'aw_onestepcheckout/onestep_helper_timer',
            'aw.onestepcheckout.relate.timer',
            $this->_timerConfig
        );
        return $block->toHtml();
    }
}