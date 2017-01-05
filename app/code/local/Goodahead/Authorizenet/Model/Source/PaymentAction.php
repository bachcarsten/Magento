<?php

class Goodahead_Authorizenet_Model_Source_PaymentAction
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Goodahead_Authorizenet_Model_Authorizenet::ACTION_AUTHORIZE,
                'label' => Mage::helper('paygate')->__('Authorize Only')
            ),
            array(
                'value' => Goodahead_Authorizenet_Model_Authorizenet::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('paygate')->__('Authorize and Capture')
            ),
        );
    }
}
