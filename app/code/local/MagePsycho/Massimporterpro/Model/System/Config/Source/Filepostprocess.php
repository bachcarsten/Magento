<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_System_Config_Source_Filepostprocess
{
    const FILE_PROCESS_TYPE_NO_ACTION = 0;
    const FILE_PROCESS_TYPE_ARCHIVE   = 1;
    const FILE_PROCESS_TYPE_DELETE    = 2;

    protected $_options;

    public function getAllOptions($withEmpty = false)
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'value' => self::FILE_PROCESS_TYPE_NO_ACTION,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('No Action'),
                ),
                array(
                    'value' => self::FILE_PROCESS_TYPE_ARCHIVE,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Move To Archive Folder'),
                ),
                array(
                    'value' => self::FILE_PROCESS_TYPE_DELETE,
                    'label' => Mage::helper('magepsycho_massimporterpro')->__('Delete'),
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