<?php

class Goodahead_CustomerCode_Model_Observer
{
    public function customerGroupEditFormLayoutUpdate($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Customer_Group_Edit_Form) {
            /* @var $form Varien_Data_Form */
            if ($form = $block->getForm()) {
                $fieldset = $form->addFieldset('promo_code_fieldset', array('legend'=>Mage::helper('goodahead_customercode')->__('Group Promo Code')));
                $customerGroup = Mage::registry('current_group');

                $promoCode = $fieldset->addField('promo_code', 'text',
                    array(
                        'name'  => 'promo_code',
                        'label' => Mage::helper('goodahead_customercode')->__('Promo Code'),
                        'title' => Mage::helper('goodahead_customercode')->__('Promo Code'),
                        'required' => false,
                    )
                );

                if ($customerGroup->getId()==0 && $customerGroup->getCustomerGroupCode() ) {
                    $promoCode->setDisabled(true);
                }

                if( Mage::getSingleton('adminhtml/session')->getCustomerGroupData() ) {
                    $form->addValues(Mage::getSingleton('adminhtml/session')->getCustomerGroupData());
                    Mage::getSingleton('adminhtml/session')->setCustomerGroupData(null);
                } else {
                    $form->addValues($customerGroup->getData());
                }
            }

        }
    }

    public function customerRegisterApplyPromoCode($observer)
    {
        if (Mage::app()->getStore()->getCode() == Mage_Core_Model_Store::ADMIN_CODE) {
            return;
        }
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        if ($customer->isObjectNew() && !$customer->hasData('_goodahead_customercode__updated')) {
            $promoCode = trim($customer->getPromoCode());
            if (strlen($promoCode)) {
                $customerGroup = Mage::getModel('customer/group')->load($promoCode,'promo_code');
                $session = Mage::getSingleton('customer/session');
                if ($customerGroup->getId()) {
                    $customer->setGroupId($customerGroup->getId());
                    $session->addSuccess(Mage::helper('goodahead_customercode')->__('Thank you for using promotional code. Your primary group is set to %s', $customerGroup->getCode()));
                } else {
                    $session->addError(Mage::helper('goodahead_customercode')->__('Provided promotional code is invalid and was ignored'));
                }
                $customer->setData('_goodahead_customercode__updated', true);
            }
        }
    }
}
