<?php

class Goodahead_LimitSmpg_Helper_Decorator_Customer_Group extends Mage_Core_Helper_Abstract
{
    protected $_methods;

    public function addShipingMethodsSelect(Mage_Adminhtml_Block_Customer_Group_Edit_Form $formBlock)
    {
        $form = $formBlock->getForm();
        $fieldset = $form->addFieldset('goodahead_limitsmpg_fieldset', array('legend'=>Mage::helper('goodahead_limitsmpg')->__('Limit Shipping Methods for This Group')));

        $select = $fieldset->addField('goodahead_limitsmpg', 'multiselect',
            array(
                'name'  => 'goodahead_limitsmpg',
                'label' => Mage::helper('goodahead_limitsmpg')->__('Shipping Methods'),
                'title' => Mage::helper('goodahead_limitsmpg')->__('Shipping Methods'),
                'values' => $this->_getMethods(),
            	'note' => Mage::helper('goodahead_limitsmpg')->__('To enable limitation, select methods allowed, or leave empty to disable limitation.'),
            )
        );

        $select->setValue(Mage::registry('goodahead_limitsmpg'));

        return $this;
    }

    protected function _getMethods()
    {
        $methods = $this->_methods;
        if (is_null($methods)) {
            $this->_methods = array();
            $res = array();
            foreach (Mage::getStoreConfig('carriers') as $code => $carrier) {
                $prefix = 'carriers/' . $code . '/';
                if (!$model = Mage::getStoreConfig($prefix . 'model')) {
                    continue;
                }
                $methodInstance = Mage::getModel($model);

                $sortOrder = (int)$methodInstance->getConfigData('sort_order');
                $methodInstance->setSortOrder($sortOrder);

                $this->_methods[] = array(
                    'value' => $code,
                    'label' => isset($carrier['title']) ? $carrier['title'] : $code,
                );
            }

        }
        return $this->_methods;
    }
}