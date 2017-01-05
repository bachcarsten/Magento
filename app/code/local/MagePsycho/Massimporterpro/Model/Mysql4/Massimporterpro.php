<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_Mysql4_Massimporterpro extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('magepsycho_massimporterpro/massimporterpro', 'entity_id');
    }
}