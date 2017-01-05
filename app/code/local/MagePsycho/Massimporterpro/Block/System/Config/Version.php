<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_System_Config_Version extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    const EXTENSION_URL = 'http://www.magepsycho.com/mass-importer-pro-price-importer-regular-special-tier-group.html';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<a href="%s" title="Mass Importer Pro" target="_blank">%s</a>', self::EXTENSION_URL, Mage::helper('magepsycho_massimporterpro')->getExtensionVersion());
    }
}
