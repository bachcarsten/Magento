<?php

class Goodahead_SalesRuleDiscount_Model_Observer
{
    public function salesRuleActionsPrepareForm($observer)
    {
        /* @var $form Varien_Data_Form */
        $form = $observer->getForm();
        $fieldset = $form->getElement('action_fieldset');

        $options = array(
            ''              => '',
        );
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection  */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
//         $collection->addFieldToFilter('frontend_input', array('eq' => 'price'));

        $select = $collection->getSelect();
        $select->where("main_table.frontend_input = 'price' OR main_table.backend_type = 'decimal'");
        $collection->addFieldToFilter('additional_table.is_used_for_promo_rules', 1);

        foreach ($collection->getItems() as $attribute) {
            $options[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $fieldset->addField('discount_attribute', 'select', array(
            'label'     => Mage::helper('goodahead_salesrulediscount')->__('Discount Value Source'),
            'name'      => 'discount_attribute',
            'options'   => $options,
            'note'      => Mage::helper('goodahead_salesrulediscount')->__('When selected, use value, provided by attribute instead of Discount Amount value'),
            'onchange'  => '$(\'rule_discount_amount\').disabled = (this.value !== \'\')',
        ), 'discount_amount');
    }

    public function applyRule($observer)
    {
        $rule = $observer->getRule();
        if (
            $rule->getSimpleAction() == 'by_fixed'
                && $rule->getDiscountAttribute()
        ) {
            $item = $observer->getItem();
            if ($item->getProduct()->getData($rule->getDiscountAttribute())) {
                $qty = $observer->getQty();
                $quoteAmount        = $observer->getQuote()->getStore()->convertPrice($item->getProduct()->getData($rule->getDiscountAttribute()));
                $discountAmount     = $qty*$quoteAmount;
                $baseDiscountAmount = $qty*$item->getProduct()->getData($rule->getDiscountAttribute());
                $result = $observer->getResult();
                $result->setData('discount_amount', $discountAmount);
                $result->setData('base_discount_amount', $baseDiscountAmount);
            }
        } elseif (
            $rule->getSimpleAction() == 'by_percent'
                && $rule->getDiscountAttribute()
        ) {
            $item = $observer->getItem();
            if ($item->getProduct()->getData($rule->getDiscountAttribute())) {
                $qty = $observer->getQty();
                $quoteAmount        = $observer->getQuote()->getStore()->convertPrice($item->getProduct()->getData($rule->getDiscountAttribute()));
                $discountAmount     = $item->getProduct()->getPrice() * $qty * $quoteAmount / 100;
                $baseDiscountAmount = $item->getProduct()->getPrice() *$qty * $item->getProduct()->getData($rule->getDiscountAttribute()) / 100;
                $result = $observer->getResult();
                $result->setData('discount_amount', $discountAmount);
                $result->setData('base_discount_amount', $baseDiscountAmount);
            }
        }
    }

}