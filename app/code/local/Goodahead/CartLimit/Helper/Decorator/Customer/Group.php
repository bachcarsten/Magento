<?php

class Goodahead_CartLimit_Helper_Decorator_Customer_Group extends Mage_Core_Helper_Abstract
{
    protected $_methods;

    public function addShipingMethodsSelect(Mage_Adminhtml_Block_Customer_Group_Edit_Form $formBlock)
    {
        $form = $formBlock->getForm();
        $fieldset = $form->addFieldset('goodahead_cartlimit_fieldset', array('legend'=>Mage::helper('goodahead_cartlimit')->__('Minimal item quantity in the cart to place an order')));

        $select = $fieldset->addField('goodahead_cartlimit', 'text',
            array(
                'name'  => 'goodahead_cartlimit',
                'label' => Mage::helper('goodahead_cartlimit')->__('Minimal quantity'),
                'title' => Mage::helper('goodahead_cartlimit')->__('Minimal quantity'),
                'note' => Mage::helper('goodahead_cartlimit')->__('Or leave blank for no restriction'),
                'class' => 'validate-number',
            )
        );

        $select->setValue(Mage::registry('goodahead_cartlimit'));

        return $this;
    }
}