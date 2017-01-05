<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('fileinformation_form', array('legend'=>Mage::helper('magepsycho_massimporterpro')->__('File Information')));

		$fieldset->addField('fileinformation_label', 'text', array(
            'name'         => 'fileinformation_label',
            'label'        => Mage::helper('magepsycho_massimporterpro')->__('Your server PHP settings allow you to upload files not more than %s at a time. Please modify post_max_size (currently is %s) and upload_max_filesize (currently is %s) values in php.ini if you want to upload larger files.<br /><br />Make sure that data encoding in the file is consistent and saved in one of supported encodings (UTF-8 or ANSI).', ini_get('upload_max_filesize'), ini_get('post_max_size'), ini_get('upload_max_filesize'))
        ));
        $form->getElement('fileinformation_label')->setRenderer(Mage::app()->getLayout()->createBlock(
            'magepsycho_massimporterpro/adminhtml_priceimporter_edit_renderer_label'
        ));

		$fieldset->addField('import_file', 'select', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Select File To Import'),
			'required'  => true,
			'name'      => 'import_file',
			'values'    => Mage::getSingleton('magepsycho_massimporterpro/system_config_source_priceimporterfiles')->getOptionsArray(),
			'note'		=> 'All files are relative to path: '.Mage::helper('magepsycho_massimporterpro')->getDefaultPriceImportDir().'.<br />If required import file is not in the dropdown list then you can either manually upload via FTP OR via below section using \'Upload File\' button.'
		));

		$_addUploadButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('add')->setLabel($this->__('Upload File'))
                ->setOnClick("addUploadFile()")->toHtml();
		$fieldset->addField('import_file_upload', 'file', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Upload File To Import'),
			'required'  => false,
			'name'      => 'import_file_upload',
			'note'		=> 'File will be uploaded to path: '.Mage::helper('magepsycho_massimporterpro')->getDefaultPriceImportDir().' and will be automatically listed in above dropdown for selection.',
			'after_element_html' => $_addUploadButtonHtml,
		));

		$form->setFieldNameSuffix('general');

		if ( Mage::getSingleton('adminhtml/session')->getPriceimporterData() ) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getPriceimporterData());
			Mage::getSingleton('adminhtml/session')->setPriceimporterData(null);
		} elseif ( Mage::registry('priceimporter_data') ) {
			$form->setValues(Mage::registry('priceimporter_data')->getData());
		}
		return parent::_prepareForm();
	}
}