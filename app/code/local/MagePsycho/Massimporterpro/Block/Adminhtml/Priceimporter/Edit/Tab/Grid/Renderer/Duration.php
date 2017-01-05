<?php
/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Block_Adminhtml_Priceimporter_Edit_Tab_Grid_Renderer_Duration extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$importDuration = $row->getImportDuration();
        if($importDuration == ""){
            return "...";
        } else{
			$seconds = ($importDuration > 1) ? ' secs' : ' sec';
            return $importDuration . $seconds;
        }
    }
}