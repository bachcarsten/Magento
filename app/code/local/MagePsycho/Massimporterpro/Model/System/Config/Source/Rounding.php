<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_System_Config_Source_Rounding
{
    const ROUNDING_NO             = 0;
    const ROUNDING_NORMAL         = 1;
    const ROUNDING_TO_NEAREST     = 2;

    protected $_options;

    public function getAllOptions($withEmpty = false)
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'value' => self::ROUNDING_NO,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('No Rounding'),
                ),
                array(
                    'value' => self::ROUNDING_NORMAL,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Round Normally'),
                ),
                array(
                    'value' => self::ROUNDING_TO_NEAREST,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Round to Nearest'),
                ),
            );

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