<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit_Tab_Data extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('dataformat_form', array('legend'=>Mage::helper('magepsycho_massimporterpro')->__('Data Format')));

		$fieldset->addField('dataformat_label', 'text', array(
            'name'         => 'dataformat_label',
            'label'        => Mage::helper('magepsycho_massimporterpro')->__("Currently Price Importer supports only the CSV file type, using a comma (,) as delimiter and double quotes(\") as enclosure."),
        ));
        $form->getElement('dataformat_label')->setRenderer(Mage::app()->getLayout()->createBlock(
            'magepsycho_massimporterpro/adminhtml_priceimporter_edit_renderer_label'
        ));

		$fieldset->addField('import_file_type', 'select', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('File Type'),
			'required'  => false,
			'name'      => 'import_file_type',
			'disabled'	=> true,
			'values'    => Mage::getSingleton('magepsycho_massimporterpro/system_config_source_filetypes')->toOptionArray(),
		));

		$fieldset->addField('delimiter', 'text', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Value Delimiter'),
			'required'  => true,
			'style'		=> 'width:3em',
			'name'      => 'delimiter',
			'value'		=> ',',
			'disabled'	=> true,
			'after_element_html' => '',
		));

		$fieldset->addField('enclosure', 'text', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Enclose Values In'),
			'required'  => true,
			'style'		=> 'width:3em',
			'name'      => 'enclosure',
			'value'		=> '"',
			'disabled'	=> true,
			'after_element_html' => '',
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