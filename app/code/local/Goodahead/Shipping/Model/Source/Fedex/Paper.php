<?php
class Goodahead_Shipping_Model_Source_Fedex_Paper
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'PAPER_4X6',
                'label' => Mage::helper('goodahead_shipping')->__('4X6')),
            array('value' => 'PAPER_4X8',
                'label' => Mage::helper('goodahead_shipping')->__('4X8')),
            array('value' => 'PAPER_4X9',
                'label' => Mage::helper('goodahead_shipping')->__('4X9')),
            array('value' => 'PAPER_7X4.75',
                'label' => Mage::helper('goodahead_shipping')->__('7X4.75')),
            array('value' => 'PAPER_8.5X11_BOTTOM_HALF_LABEL',
                'label' => Mage::helper('goodahead_shipping')->__('8.5X11_BOTTOM_HALF_LABEL')),
            array('value' => 'PAPER_8.5X11_TOP_HALF_LABEL',
                'label' => Mage::helper('goodahead_shipping')->__('8.5X11_TOP_HALF_LABEL')),
            array('value' => 'PAPER_LETTER',
                'label' => Mage::helper('goodahead_shipping')->__('LETTER'))

        );
    }
}