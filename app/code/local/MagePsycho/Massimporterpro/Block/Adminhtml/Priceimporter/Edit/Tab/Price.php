<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit_Tab_Price extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('pricesettings_form', array('legend'=>Mage::helper('magepsycho_massimporterpro')->__('Price Settings')));

        $fieldset->addField('priceinformation_label', 'text', array(
            'name'         => 'priceinformation_label',
            'label'        => Mage::helper('magepsycho_massimporterpro')->__('You can change the default Price Settings from <a href="%s">System > Configuration > Mass Importer Pro > Price Settings</a>.', Mage::helper("adminhtml")->getUrl('adminhtml/system_config/edit/section/magepsycho_massimporterpro'))
        ));
        $form->getElement('priceinformation_label')->setRenderer(Mage::app()->getLayout()->createBlock(
            'magepsycho_massimporterpro/adminhtml_priceimporter_edit_renderer_label'
        ));

        $fieldset->addField('price_rounding', 'select', array(
            'label'     => Mage::helper('magepsycho_massimporterpro')->__('Pricing Rounding'),
            'required'  => false,
            'name'      => 'price_rounding',
            'onchange'	=> 'showHideNearestPricingRow(this.value)',
            'values'    => Mage::getSingleton('magepsycho_massimporterpro/system_config_source_rounding')->toOptionArray(),
            'note'		=> '<strong>Round normally</strong>: 9.43 -> 9.00, 9.63 -> 10.00 <br /><strong>Round to Nearest</strong>: 9.43 -> 9.00 + (value=0.5) = 9.50, 9.43 -> 9.00 + (value=0.99) = 9.99',
        ));

        $fieldset->addField('rounding_nearest', 'text', array(
            'label'     => Mage::helper('magepsycho_massimporterpro')->__('Rounding Value'),
            'required'  => false,
            'name'      => 'rounding_nearest',
            'note'		=> 'If value is 0.99: 9.43 -> 9.99, 0.5: 9.43 -> 9.5, 0: 9.43 -> 9.00',
        ));

		$fieldset->addField('tier_price_import_type', 'select', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Tier Price Import Type'),
			'required'  => false,
			'name'      => 'tier_price_import_type',
			'values'    => Mage::getSingleton('magepsycho_massimporterpro/system_config_source_importtypes')->toOptionArray(),
			'note'		=> '<strong>Merge</strong>: Merge with the existing data, <br /><strong>Replace (Group)</strong>: Delete existing data by sku & a group and insert new, <br /><strong>Replace (All)</strong>: Delete existing data by sku & all groups and insert new',
		));

		$fieldset->addField('group_price_import_type', 'select', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Group Price Import Type'),
			'required'  => false,
			'name'      => 'group_price_import_type',
			'values'    => Mage::getSingleton('magepsycho_massimporterpro/system_config_source_importtypes')->toOptionArray(),
			'note'		=> 'The \'Group Price\' feature is only available in Magento 1.7 or higher.',
		));

		$fieldset->addField('reindex_after_import', 'select', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Re-Index Product Prices After Import'),
			'required'  => false,
			'name'      => 'reindex_after_import',
			'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
			'note'		=> 'Re-Indexing may take couple of minutes or more, depending upon the no of catalog products.',
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