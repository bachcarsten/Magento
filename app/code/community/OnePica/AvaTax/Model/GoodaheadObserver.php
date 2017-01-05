<?php
/**
 * @author     Goodahead
 */


class OnePica_AvaTax_Model_GoodaheadObserver extends Mage_Core_Model_Abstract
{
    public function initSystemConfig($observer)
    {
        if(Mage::helper('avatax')->isAnyStoreDisabled()) {
            $config = $observer->getEvent()->getConfig();

            //these 4 lines are the only added content
            $configFile = Mage::helper('avatax')->getEtcPath() . DS . 'system-disabled.xml';
            $mergeModel = Mage::getModel('core/config_base');
            $mergeModel->loadFile($configFile);
            $config->extend($mergeModel, true);
        }
    }


    public function adminhtmlOrderCreateProcessData($observer)
    {
        if (Mage::helper('avatax')->isAvataxEnabled()) {
            if (!Mage::app()->getFrontController()->getRequest()->getParam('isAjax')) {
                $orderCreateMessageAdded = Mage::registry('orderCreateMessageAdded');
                $orderCreateModel = $observer->getEvent()->getOrderCreateModel();

                $result = $orderCreateModel->getShippingAddress()->validate();
                if ($result !== true) {
                    $storeId = $orderCreateModel->getSession()->getStore()->getId();
                    if(Mage::helper('avatax')->fullStopOnError($storeId)) {
                        foreach ($result as $error) {
                            $orderCreateModel->getSession()->addError($error);
                        }
                        Mage::throwException('');
                    }
                }
                else if ($orderCreateModel->getShippingAddress()->getAddressNormalized() && !$orderCreateMessageAdded) {
                    Mage::getSingleton('avatax/session')->addNotice(Mage::helper('avatax')->__('The shipping address has been modified during the validation process.  Please confirm the address below is accurate.'));

                    Mage::register('orderCreateMessageAdded', true);
                }
            }
        }
    }


    public function blockPrepareLayoutBefore($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Tax_Class_Grid) {
            $url = Mage::helper('avatax')->getDocumentationUrl();
            Mage::helper('adminhtml')->setPageHelpUrl($url);

            $block->addColumn('op_avatax_code',
                array(
                    'header'    => Mage::helper('avatax')->__('AvaTax Code'),
                    'align'     => 'left',
                    'index'     => 'op_avatax_code',
                    'width'		=> '175px'
                )
            );
        }
    }


    public function blocktoHtmlBefore($observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Tax_Class_Edit_Form) {
            $fieldset = $block->getForm()->getElement('base_fieldset');

            $model  = Mage::registry('tax_class');
            $fieldset->addField('op_avatax_code', 'text',
                array(
                    'name'  => 'op_avatax_code',
                    'label' => Mage::helper('avatax')->__('AvaTax Code'),
                    'value' => $model->getOpAvataxCode(),
                ),
                'class_name');
        }
    }
}