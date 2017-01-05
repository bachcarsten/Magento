<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonCategoryEditForm');
        //------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->marketplace_id = $this->getRequest()->getParam('marketplace_id');
        $this->listing_product_ids = $this->getRequest()->getParam('listing_product_ids');

        $this->nodes = json_decode($connRead->select()
                                            ->from(Mage::getSingleton('core/resource')
                                                        ->getTableName('m2epro_amazon_dictionary_marketplace'),'nodes')
                                            ->where('marketplace_id = ?', $this->marketplace_id)
                                            ->query()
                                            ->fetchColumn(),true);

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'category_confirm_button',
                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => 'AmazonCategoryHandlerObj.confirmCategory();',
            ) );
        $this->setChild('category_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'category_change_button',
                'label'   => Mage::helper('M2ePro')->__('Change Category'),
                'onclick' => 'AmazonCategoryHandlerObj.changeCategory();',
            ) );
        $this->setChild('category_change_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'category_cancel_button',
                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                'onclick' => 'AmazonCategoryHandlerObj.cancelCategory();',
            ) );
        $this->setChild('category_cancel_button',$buttonBlock);
        //------------------------------

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        $this->setData('attributes_sets', $attributesSets);

        $temp = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $this->attribute_set_locked = false;

        if (!is_null($temp)) {
            $this->attribute_set_locked = (bool)Mage::getModel('M2ePro/Amazon_Listing_Product')->getCollection()
                 ->addFieldToFilter('category_id',$temp['category']['id'])
                 ->getSize();
        }

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'attribute_sets_select_all_button',
                                'label'   => Mage::helper('M2ePro')->__('Select All'),
                                'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
                                'class'   => 'attribute_sets_select_all_button'
                            ) );
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'attribute_sets_confirm_button',
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'AmazonCategoryHandlerObj.confirmAttributeSets();',
                                'class'   => 'attribute_sets_confirm_button',
                                'style'   => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}