<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_System_Config_Source_Importvia
{
    const IMPORT_VIA_WEB         = 'web';
    const IMPORT_VIA_CRON        = 'cron';
    const IMPORT_VIA_SHELL       = 'shell';

    protected $_options;

    public function getAllOptions($withEmpty = false)
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'value' => self::IMPORT_VIA_WEB,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Web'),
                ),
                array(
                    'value' => self::IMPORT_VIA_CRON,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Cron'),
                ),
                array(
                    'value' => self::IMPORT_VIA_SHELL,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Shell'),
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