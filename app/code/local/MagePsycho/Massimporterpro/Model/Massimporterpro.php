<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_Massimporterpro extends Mage_Core_Model_Abstract
{
    const ENTITY    = 'magepsycho_massimporterpro';
    const CACHE_TAG = 'magepsycho_massimporterpro';

    protected $_eventPrefix = 'magepsycho_massimporterpro';
    protected $_eventObject = 'massimporterpro';


    public function _construct()
    {
        parent::_construct();
        $this->_init('magepsycho_massimporterpro/massimporterpro');
    }
}