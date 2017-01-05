<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Description_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateDescriptionEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template_description';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Ess_M2ePro_Helper_Component_Amazon::TITLE;
        } else {
            $componentName = '';
        }

        if (Mage::helper('M2ePro')->getGlobalValue('temp_data')
            && Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
        ) {
            $headerText = Mage::helper('M2ePro')->__("Edit%component_name% Description Template");
            $headerText .= ' "'.$this->escapeHtml(Mage::helper('M2ePro')->getGlobalValue('temp_data')->getTitle()).'"';
        } else {
            $headerText = Mage::helper('M2ePro')->__("Add%component_name% Description Template");
        }
        $this->_headerText = str_replace('%component_name%',$componentName,$headerText);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'AmazonTemplateDescriptionHandlerObj.back_click(\''
                           .Mage::helper('M2ePro')->getBackUrl('list').'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'AmazonTemplateDescriptionHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        if (Mage::helper('M2ePro')->getGlobalValue('temp_data')
            && Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
        ) {

            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick'   => 'AmazonTemplateDescriptionHandlerObj.duplicate_click(\'amazon-template-description\')',
                'class'     => 'add M2ePro_duplicate_button'
            ));

            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'AmazonTemplateDescriptionHandlerObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
            ));
        }

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'AmazonTemplateDescriptionHandlerObj.save_click()',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'AmazonTemplateDescriptionHandlerObj.save_and_edit_click()',
            'class'     => 'save'
        ));
        //------------------------------
    }
}