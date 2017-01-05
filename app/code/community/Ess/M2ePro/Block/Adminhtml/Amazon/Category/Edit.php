<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonCategoryEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_category';
        $this->_mode = 'edit';
        //------------------------------

        $marketplace_id      = $this->getRequest()->getParam('marketplace_id');
        $listing_product_ids = $this->getRequest()->getParam('listing_product_ids');

        // Set header text
        //------------------------------
        $marketplaceInstance = Mage::helper('M2ePro/Component_Amazon')->getMarketplace($marketplace_id);

        if ($this->getRequest()->getParam('id')) {
            $headerText = Mage::helper('M2ePro')->__('Edit "New ASIN" Template For "%code%" Marketplace');
        } else {
            $headerText = Mage::helper('M2ePro')->__('Add "New ASIN" Template For "%code%" Marketplace');
        }
        $this->_headerText = str_replace(
            '%code%',
            $marketplaceInstance->getCode(),
            $headerText
        );
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\'' . Mage::helper('M2ePro')->getBackUrl().'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CommonHandlerObj.save_click(\''.$this->getUrl('*/adminhtml_amazon_category/add',array(
                'marketplace_id' => $marketplace_id,
                'listing_product_ids' => $listing_product_ids
            )).'\')',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_map', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Assign'),
            'onclick'   => 'CommonHandlerObj.save_click(\''.$this->getUrl('*/adminhtml_amazon_category/add',array(
                'marketplace_id' => $marketplace_id,
                'listing_product_ids' => $listing_product_ids,
                'do_map' => true
            )).'\')',
            'class'     => 'save'
        ));
        //------------------------------
    }
}