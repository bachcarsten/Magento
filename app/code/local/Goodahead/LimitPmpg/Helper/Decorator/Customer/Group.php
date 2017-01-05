<?php

class Goodahead_LimitPmpg_Helper_Decorator_Customer_Group extends Mage_Core_Helper_Abstract
{
    protected $_methods;

    public function addPaymentMethodsSelect(Mage_Adminhtml_Block_Customer_Group_Edit_Form $formBlock)
    {
        $form = $formBlock->getForm();
        $fieldset = $form->addFieldset('goodahead_limitpmpg_fieldset', array('legend'=>Mage::helper('goodahead_limitpmpg')->__('Limit Payment Methods for This Group')));

        $select = $fieldset->addField('goodahead_limitpmpg', 'multiselect',
            array(
                'name'  => 'goodahead_limitpmpg',
                'label' => Mage::helper('goodahead_limitpmpg')->__('Payment Methods'),
                'title' => Mage::helper('goodahead_limitpmpg')->__('Payment Methods'),
                'values' => $this->_getMethods(),
            	'note' => Mage::helper('goodahead_limitpmpg')->__('To enable limitation, select methods allowed, or leave empty to disable limitation.'),
           )
        );

        $select->setValue(Mage::registry('goodahead_limitpmpg'));

        return $this;
    }

    protected function _getMethods()
    {
        $methods = $this->_methods;
        if (is_null($methods)) {
            $this->_methods = array();
            $res = array();
            foreach (Mage::helper('payment')->getPaymentMethods() as $code => $methodConfig) {
                $prefix = Mage_Payment_Helper_Data::XML_PATH_PAYMENT_METHODS . '/' . $code . '/';
                if (!$model = Mage::getStoreConfig($prefix . 'model')) {
                    continue;
                }
                $methodInstance = Mage::getModel($model);

                $sortOrder = (int)$methodInstance->getConfigData('sort_order');
                $methodInstance->setSortOrder($sortOrder);
                
                $res[] = $methodInstance;
            }
    
            $methods = $res;
            foreach ($methods as $_method) {
                $this->_methods[] = array(
                    'value' => $_method->getCode(),
                    'label' => $_method->getTitle() ? $_method->getTitle() : $_method->getCode(),
                );
            }
        }
        return $this->_methods;
    }
}