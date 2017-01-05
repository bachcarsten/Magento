<?php
class Goodahead_OrderBySku_Block_Account extends Mage_Core_Block_Template
{
    /**
     * Retrieve form action url
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('*/*/add');
    }

//    public function getSampleFileUrl()
//    {
//        return Mage::getUrl('*/*/download');
//    }
}