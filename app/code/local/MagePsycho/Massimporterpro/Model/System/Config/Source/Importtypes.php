<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_System_Config_Source_Importtypes
{
    const IMPORT_TYPE_MERGE         = 'merge';
    const IMPORT_TYPE_REPLACE_GROUP = 'replace_group';
    const IMPORT_TYPE_REPLACE_ALL   = 'replace_all';

    protected $_options;

    public function getAllOptions($withEmpty = false)
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'value' => self::IMPORT_TYPE_MERGE,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Merge'),
                ),
                array(
                    'value' => self::IMPORT_TYPE_REPLACE_GROUP,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Replace (Group)'),
                ),
                array(
                    'value' => self::IMPORT_TYPE_REPLACE_ALL,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Replace (All)'),
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