<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_System_Config_Source_Massimporterpro
{
    protected $_options;

    public function getAllOptions($withEmpty = false)
    {
        if (is_null($this->_options)) {
            $this->_options = Mage::getResourceModel('magepsycho_massimporterpro/massimporterpro_collection')->loadData()->toOptionArray(false);

        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value' => '', 'label' => ''));
        }
        return $options;
    }

    public function getOptionsArray($withEmpty = true)
    {
        $options = array();
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);
        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}