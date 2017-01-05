<?php

/**
 * @category   MagePsycho
 * @package    MagePsycho_Massimporterpro
 * @author     magepsycho@gmail.com
 * @website    http://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagePsycho_Massimporterpro_Model_Observer
{
    public function controllerActionPredispatch(Varien_Event_Observer $observer)
    {
        $helper           = Mage::helper('magepsycho_massimporterpro');
        $isValid          = $helper->isValid();
        $isActive         = $helper->isActive();
        $adminhtmlSession = Mage::getSingleton('adminhtml/session');
        $fullActionName   = $observer->getEvent()->getControllerAction()->getFullActionName();
        if (!$isActive && 'adminhtml_priceimporter_index' == $fullActionName) {
            $adminhtmlSession->getMessages(true);
            $adminhtmlSession->addNotice(base64_decode('RXh0ZW5zaW9uIGhhcyBiZWVuIGRpc2FibGVkLiBQbGVhc2UgZW5hYmxlIGl0IGZyb20gTWFzcyBJbXBvcnRlciBQcm8gJnJhcXVvOyBNYW5hZ2UgU2V0dGluZ3M='));
            return;
        } else if ($isActive && !$isValid && 'adminhtml_priceimporter_index' == $fullActionName) {
            $adminhtmlSession->getMessages(true);
            $adminhtmlSession->addError($helper->getMessage());
            return;
        }
        return $this;
    }

    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        $helper   = Mage::helper('magepsycho_massimporterpro');
        $isValid  = $helper->isValid();
        $isActive = $helper->isActive();
        if (!$isActive && !$isValid) {
            return;
        }

        /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();

        // Check if there is a massaction block and if yes, add the massaction
        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction) {
            if ($block->getParentBlock() instanceof Mage_Adminhtml_Block_Catalog_Product_Grid) {
                $block->addItem('mp_export_prices_separator', array(
                    'label'      => Mage::helper('magepsycho_massimporterpro')->__('-------------------------'),
                    'url'        => null,
                    'additional' => array()
                ));

                $priceTypes = array(
                    '0' => 'All',
                    '1' => 'Regular Price',
                    '2' => 'Special Price',
                    '3' => 'Regular / Special Price',
                    '4' => 'Tier Price'
                );
                if ($helper->checkVersion('1.7')) {
                    $priceTypes['5'] = 'Group Price';
                    $priceTypes['6'] = 'Tier / Group Price';
                }
                $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
                $block->addItem('mp_export_prices', array(
                    'label'      => Mage::helper('magepsycho_massimporterpro')->__('Export Prices'),
                    'url'        => Mage::getUrl('adminhtml/catalog_product/massExportPrices', array('store' => $storeId)),
                    'additional' => array(
                        'types' => array(
                            'name'   => 'price_type',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => Mage::helper('magepsycho_massimporterpro')->__('Price Type'),
                            'values' => $priceTypes
                        )
                    )
                ));
            }
        }
    }
}