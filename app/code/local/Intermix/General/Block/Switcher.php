<?php
class Intermix_General_Block_Switcher
    extends Mage_Core_Block_Template
{
    public function getWebsiteUrl($code)
    {
        try {
            $website = Mage::getModel('core/website')->load($code);
            if ($website->getId()) {
                $storeId = $website->getDefaultStore()->getId();
                $url = Mage::getStoreConfig('web/unsecure/base_url', $storeId);
                return $url;
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}