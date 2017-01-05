<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_Cmd_SystemController
    extends Ess_M2ePro_Controller_Adminhtml_Cmd_SynchronizationController
{
    //#############################################

    /**
     * @title "PHP Info"
     * @description "View server phpinfo() information"
     * @group "System"
     * @new_line
     */
    public function phpInfoAction()
    {
        if ($this->getRequest()->getParam('frame')) {
            phpinfo();
            return;
        }

        $this->printBack();
        $urlPhpInfo = $this->getUrl('*/*/*', array('frame' => 'yes'));
        echo '<iframe src="' . $urlPhpInfo . '" style="width:100%; height:90%;" frameborder="no"></iframe>';
    }

    //#############################################

    /**
     * @title "ESS Configuration"
     * @description "Go to ess configuration edit page"
     * @group "System"
     */
    public function goToEditEssConfigAction()
    {
        $this->_redirect('*/adminhtml_config/ess');
    }

    /**
     * @title "M2ePro Configuration"
     * @description "Go to m2epro configuration edit page"
     * @group "System"
     * @new_line
     */
    public function goToEditM2eProConfigAction()
    {
        $this->_redirect('*/adminhtml_config/m2epro');
    }

    //#############################################

    /**
     * @title "Run Cron"
     * @description "Emulate starting cron"
     * @group "System"
     */
    public function runCronAction()
    {
        Mage::getModel('M2ePro/Cron')->process();
    }

    /**
     * @title "Update License"
     * @description "Send update license request to server"
     * @group "System"
     * @new_line
     */
    public function licenseUpdateAction()
    {
        Mage::getModel('M2ePro/License_Server')->updateStatus(true);
        Mage::getModel('M2ePro/License_Server')->updateLock(true);
        Mage::getModel('M2ePro/License_Server')->updateMessages(true);

        Mage::helper('M2ePro')->setSessionValue('success_message', 'License status was successfully updated.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //#############################################

    /**
     * @title "Clear COOKIES"
     * @description "Clear all current cookies"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearCookiesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Cookies was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //----------------------------------------------

    /**
     * @title "Clear Extension Cache"
     * @description "Clear extension cache"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearExtensionCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearCache();
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Extension cache was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Clear Magento Cache"
     * @description "Clear magento cache"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearMagentoCacheAction()
    {
        Mage::helper('M2ePro/Magento')->clearCache();
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Magento cache was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //#############################################
}