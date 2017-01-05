<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId   = 'id';
        $this->_blockGroup = 'magepsycho_massimporterpro';
        $this->_controller = 'adminhtml_priceimporter';

        $this->removeButton('back');
        $helper   = Mage::helper('magepsycho_massimporterpro');
        $isValid  = $helper->isValid();
        $isActive = $helper->isActive();

        $message = '';
        if (!$isActive) {
            $message = base64_decode('RXh0ZW5zaW9uIGhhcyBiZWVuIGRpc2FibGVkLiBQbGVhc2UgZW5hYmxlIGl0IGZyb20gTWFzcyBJbXBvcnRlciBQcm8gJnJhcXVvOyBNYW5hZ2UgU2V0dGluZ3M=');
        } else if ($isActive && !$isValid) {
            $message = base64_decode('UGxlYXNlIGVudGVyIGEgdmFsaWQgbGljZW5zZSBrZXkgaW4gb3JkZXIgdG8gcnVuIHRoZSBleHRlbnNpb24=');
        }
        if (!$isActive || ($isActive && !$isValid)) {
            $this->removeButton('check');
            $this->removeButton('save');
            $this->_addButton('dcheck', array(
                'label'   => Mage::helper('magepsycho_massimporterpro')->__('Check Import'),
                'onclick' => 'alert(\'' . $message . '.\')',
                'class'   => 'disabled',
            ), -100, 1);
            $this->_addButton('dsave', array(
                'label'   => Mage::helper('magepsycho_massimporterpro')->__('Run Import'),
                'onclick' => 'alert(\'' . $message . '.\')',
                'class'   => 'disabled',
            ), -100, 2);
        } else {
            $this->_addButton('check', array(
                'label'   => Mage::helper('magepsycho_massimporterpro')->__('Check Import'),
                'onclick'   => 'checkCsvEdit()',
                'class'   => 'save',
            ), 1, 1);

            $this->_updateButton('save', 'label', Mage::helper('magepsycho_massimporterpro')->__('Run Import'));
        }

        $roundType = $helper->getConfig('price_rounding', 'price_settings');
        if (Mage::registry('priceimporter_data') && Mage::registry('priceimporter_data')->getId()) {
            $roundType = Mage::registry('priceimporter_data')->getPriceRounding();
        }

        $this->_formScripts[] = "
            function checkCsvEdit(){
                editForm.submit('" . Mage::helper('adminhtml')->getUrl('adminhtml/priceimporter/checkCsv') . "');
            }
            function addUploadFile(){
				var importFile = $('import_file_upload').value;
				if(importFile == ''){
					alert('Please select some import file.');
					return;
				}

				var ext = importFile.substring(importFile.lastIndexOf('.') + 1);
				if(ext.toLowerCase() != 'csv'){
					alert('Please select valid import file (CSV).');
					return;
				}

				//tweak for required feilds: import file section, delimiter, enclosed
				$('import_file').removeClassName('required-entry');
				$('delimiter').removeClassName('required-entry');
				$('enclosure').removeClassName('required-entry');
                editForm.submit('" . Mage::helper('adminhtml')->getUrl('adminhtml/priceimporter/uploadCsv') . "');
            }
        ";
        $this->_formScripts[] = "
			function showHideNearestPricingRow(value){
				var roundingNearest	= $('rounding_nearest');
				if(value == 2){
					roundingNearest.up().up().show();
				} else {
				    roundingNearest.up().up().hide();
			    }
			}
			function showHideNearestPricingRowOnLoad(){
				showHideNearestPricingRow('" . $roundType . "');
			}
			window.onload = showHideNearestPricingRowOnLoad;
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('priceimporter_data') && Mage::registry('priceimporter_data')->getId()) {
            return Mage::helper('magepsycho_massimporterpro')->__('Price Importer');
        } else {
            return Mage::helper('magepsycho_massimporterpro')->__('Price Importer');
        }
    }
}