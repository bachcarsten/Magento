<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfig($field, $section = 'option', $default = null)
    {
        $value = Mage::getStoreConfig('magepsycho_massimporterpro/' . $section . '/' . $field);
        if (!isset($value) or trim($value) == '') {
            return $default;
        } else {
            return $value;
        }
    }

    public function log($data, $includeSep = false)
    {
        if (!$this->getConfig('enable_log')) {
            return;
        }
        if ($includeSep) {
            $separator = '==========================================================================';
            Mage::log($separator, null, 'magepsycho_massimporterpro.log', true);
        }
        Mage::log($data, null, 'magepsycho_massimporterpro.log', true);
    }

    function __construct()
    {
        $field = base64_decode('ZG9tYWluX3R5cGU=');
        if ($this->getConfig($field) == 1) {
            $key        = base64_decode('cHJvZF9saWNlbnNl');
            $this->mode = base64_decode('cHJvZHVjdGlvbg==');
        } else {
            $key        = base64_decode('ZGV2X2xpY2Vuc2U=');
            $this->mode = base64_decode('ZGV2ZWxvcG1lbnQ=');
        }
        $this->temp = $this->getConfig($key);
    }

    public function getMessage()
    {
        $message = base64_decode('WW91IGFyZSB1c2luZyB1bmxpY2Vuc2VkIHZlcnNpb24gb2YgJ01hc3MgSW1wb3J0ZXIgUHJvOiBQcmljZSBJbXBvcnRlcicgRXh0ZW5zaW9uIGZvciBkb21haW46IHt7RE9NQUlOfX0uIFBsZWFzZSBlbnRlciBhIHZhbGlkIExpY2Vuc2UgS2V5IGZyb20gU3lzdGVtICZyYXF1bzsgQ29uZmlndXJhdGlvbiAmcmFxdW87IE1hZ2VQc3ljaG8gRXh0ZW5zaW9ucyAmcmFxdW87IE1hc3MgSW1wb3J0ZXIgUHJvICZyYXF1bzsgTGljZW5zZSBLZXkuIElmIHlvdSBkb24ndCBoYXZlIG9uZSwgcGxlYXNlIHB1cmNoYXNlIGEgdmFsaWQgbGljZW5zZSBmcm9tIDxhIGhyZWY9Imh0dHA6Ly93d3cubWFnZXBzeWNoby5jb20vY29udGFjdHMiIHRhcmdldD0iX2JsYW5rIj53d3cubWFnZXBzeWNoby5jb208L2E+IG9yIHlvdSBjYW4gZGlyZWN0bHkgZW1haWwgdG8gPGEgaHJlZj0ibWFpbHRvOmluZm9AbWFnZXBzeWNoby5jb20iPmluZm9AbWFnZXBzeWNoby5jb208L2E+');
        $message = str_replace('{{DOMAIN}}', $this->getDomain(), $message);
        return $message;
    }

    public function getDomain()
    {
        $domain     = Mage::getBaseUrl();
        $baseDomain = Mage::helper('magepsycho_massimporterpro/url')->getBaseDomain($domain);
        return strtolower($baseDomain);
    }

    public function checkEntry($domain, $serial)
    {
        $salt = sha1(base64_decode('bWFzc2ltcG9ydGVycHJv'));
        if (sha1($salt . $domain . $this->mode) == $serial) {
            return true;
        }
        return false;
    }

    public function isValid()
    {
        $temp = $this->temp;
        if (!$this->checkEntry($this->getDomain(), $temp)) {
            return false;
        }
        return true;
    }

    public function isActive()
    {
        return (bool)$this->getConfig('active');
    }

    public function displayFormattedArray(&$array, $maxLevel = 10, $stack = array())
    {
        if (is_array($array) || is_object($array)) {
            if (in_array($array, $stack, true)) {
                echo "<font color=red>RECURSION</font>";
                return;
            }
            $stack[] =& $array;
            if ($maxLevel < 1) {
                echo "<font color=red>nivel maximo alcanzado</font>";
                return;
            }
            $maxLevel--;
            echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
            if (is_array($array)) {
                #echo '<tr><td colspan=2 style="background-color:#333333;" height="1"><strong><font color=white></font></strong></td></tr>';
            } else {
                echo '<tr><td colspan=2 style="background-color:#333333;"><strong>';
                echo '<font color=white>OBJECT Type: ' . get_class($array) . '</font></strong></td></tr>';
            }
            $color = 0;
            foreach ($array as $k => $v) {
                if ($maxLevel % 2) {
                    $rgb = ($color++ % 2) ? "#888888" : "#BBBBBB";
                } else {
                    $rgb = ($color++ % 2) ? "#8888BB" : "#BBBBFF";
                }
                echo '<tr><td valign="top" style="width:40px;background-color:' . $rgb . ';">';
                echo '<strong>' . $k . "</strong></td><td>";
                $this->displayFormattedArray($v, $maxLevel, $stack);
                echo "</td></tr>";
            }
            echo "</table>";
            return;
        }
        if ($array === null) {
            echo "<font color=green>NULL</font>";
        } elseif ($array === 0) {
            echo "0";
        } elseif ($array === true) {
            echo "<font color=green>TRUE</font>";
        } elseif ($array === false) {
            echo "<font color=green>FALSE</font>";
        } elseif ($array === "") {
            echo "<font color=green>EMPTY</font>";
        } else {
            echo str_replace("\n", "<strong><font color=red>*</font></strong><br>\n", $array);
        }
    }

    public function getMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function getParseType()
    {
        return 'csv';
    }

    public function getImportedFiles($importType)
    {
        $files = array();
        $path  = Mage::app()->getConfig()->getTempVarDir() . '/magepsycho/massimporterpro/' . $importType . '/web';
        if (!is_readable($path)) {
            return $files;
        }
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if ($entry != '.'
                && $entry != '..'
                && in_array(strtolower(substr($entry, strrpos($entry, '.') + 1)), array($this->getParseType()))
            ) {
                $files[] = $entry;
            }
        }
        sort($files);
        $dir->close();
        return $files;
    }

    public function checkVersion($version, $operator = '>=')
    {
        return version_compare(Mage::getVersion(), $version, $operator);
    }

    public function getExtensionVersion()
    {
        $moduleCode = 'MagePsycho_Massimporterpro';
        return (string)$currentVer = Mage::getConfig()->getModuleConfig($moduleCode)->version;
    }

    function slugify($str, $separator = 'dash')
    {
        if ($separator == 'dash') {
            $search  = '_';
            $replace = '-';
        } else {
            $search  = '-';
            $replace = '_';
        }

        $trans = array(
            $search                     => $replace,
            "\s+"                       => $replace,
            "[^a-z0-9" . $replace . "]" => '',
            $replace . "+"              => $replace,
            $replace . "$"              => '',
            "^" . $replace              => ''
        );

        $str = strip_tags(strtolower($str));

        foreach ($trans as $key => $val) {
            $str = preg_replace("#" . $key . "#", $val, $str);
        }

        return trim(stripslashes($str));
    }

    public function getCustomerGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            // ->setRealGroupsFilter()
            ->loadData()
            ->toOptionArray();
        return $customerGroups;
    }

    public function getDefaultPriceCronDir()
    {
        return 'var/magepsycho/massimporterpro/price_importer/cron';
    }

    public function getDefaultPriceShellDir()
    {
        return 'var/magepsycho/massimporterpro/price_importer/shell';
    }

    public function getDefaultPriceImportDir()
    {
        return 'var/magepsycho/massimporterpro/price_importer/web';
    }

    public function getDefaultPriceArchiveDir()
    {
        return 'var/magepsycho/massimporterpro/price_importer/archive';
    }
}