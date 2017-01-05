<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_System_Config_Backend_Priceimporter_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/massimporterpro_price_importer/schedule/cron_expr';
    const CRON_MODEL_PATH  = 'crontab/jobs/massimporterpro_price_importer/run/model';

    protected function _afterSave()
    {
        $helper = Mage::helper('magepsycho_massimporterpro');

        $enabled    = $this->getData('groups/price_settings/fields/enable_cron/value');
        $time       = $this->getData('groups/price_settings/fields/time/value');
        $frequency  = $this->getData('groups/price_settings/fields/frequency/value');
        $errorEmail = $this->getData('groups/price_settings/fields/error_email/value');

        $frequencyDaily   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly  = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronDayOfWeek  = date('N');
            $cronExprArray  = array(
                intval($time[1]), # Minute
                intval($time[0]), # Hour
                ($frequency == $frequencyMonthly) ? '1' : '*', # Day of the Month
                '*', # Month of the Year
                ($frequency == $frequencyWeekly) ? '1' : '*', # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string)Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }

}