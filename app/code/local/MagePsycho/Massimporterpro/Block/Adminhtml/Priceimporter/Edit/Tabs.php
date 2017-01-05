<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('priceimporter_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('magepsycho_massimporterpro')->__('Import Information'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('file_section', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('File Information'),
			'title'     => Mage::helper('magepsycho_massimporterpro')->__('File Information'),
			'content'   => $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_form')->toHtml(),
		));

		$this->addTab('dataformat_section', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Data Format'),
			'title'     => Mage::helper('magepsycho_massimporterpro')->__('Data Format'),
			'content'   => $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_data')->toHtml(),
		));

		$this->addTab('price_section', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Price Settings'),
			'title'     => Mage::helper('magepsycho_massimporterpro')->__('Price Settings'),
			'content'   => $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_price')->toHtml()
		));

		$this->addTab('history_section', array(
			'label'     => Mage::helper('magepsycho_massimporterpro')->__('Import History'),
			'title'     => Mage::helper('magepsycho_massimporterpro')->__('Import History'),
			'content'   => $this->getLayout()->createBlock('magepsycho_massimporterpro/adminhtml_priceimporter_edit_tab_history')->toHtml()
		));

		return parent::_beforeToHtml();
	}

}