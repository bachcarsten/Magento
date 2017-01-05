<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Category_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonCategoryEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/category/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $this->listing_product_ids = $this->getRequest()->getParam('listing_product_ids');

        $this->nodes = json_decode($connRead->select()
                                            ->from(Mage::getSingleton('core/resource')
                                                        ->getTableName('m2epro_amazon_dictionary_marketplace'),'nodes')
                                            ->where('marketplace_id = ?', $this->marketplaceId)
                                            ->query()
                                            ->fetchColumn(),true);
        !is_array($this->nodes) && $this->nodes = array();

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
                                'onclick' => '',
                                'class'   => 'attribute_sets_confirm_button',
                                'style'   => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'browse_category_button',
                                'label'   => Mage::helper('M2ePro')->__('Browse'),
                                'onclick' => 'AmazonCategoryHandlerObj.browse_category.showCenter(true)'
                            ) );
        $this->setChild('browse_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'search_category_button',
                                'label'   => Mage::helper('M2ePro')->__('Search'),
                                'onclick' => 'AmazonCategoryHandlerObj.search_category.showCenter(true)',
                            ) );
        $this->setChild('search_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'search_category_popup_button',
                                'label'   => Mage::helper('M2ePro')->__('Search'),
                                'onclick' => 'AmazonCategoryHandlerObj.searchClick()',
                            ) );
        $this->setChild('search_category_popup_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'reset_category_popup_button',
                                'label'   => Mage::helper('M2ePro')->__('Reset'),
                                'onclick' => 'AmazonCategoryHandlerObj.resetSearchClick()',
                            ) );
        $this->setChild('reset_category_popup_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'close_browse_popup_button',
                                'label'   => Mage::helper('M2ePro')->__('Close'),
                                'onclick' => 'AmazonCategoryHandlerObj.browse_category.close()',
                            ) );
        $this->setChild('close_browse_popup_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'close_search_popup_button',
                                'label'   => Mage::helper('M2ePro')->__('Close'),
                                'onclick' => 'AmazonCategoryHandlerObj.search_category.close()',
                            ) );
        $this->setChild('close_search_popup_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}