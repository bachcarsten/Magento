<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit_Renderer_Label extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
         $html = '<tr><td colspan="2"><ul class="messages"><li class="notice-msg"><ul><li><span>' . $element->getLabel() . '</span></li></ul></li></ul></td></tr>';
        return $html;
    }
}