<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Category extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Category_Description
     */
    private $descriptionTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Category');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::helper('M2ePro/Component_Amazon')
                            ->getCollection('Listing_Product')
                            ->addFieldToFilter('category_id', $this->getId())
                            ->addFieldToFilter('status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
                            ->getSize();
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        /* @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingProductTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        $writeConnection->update(
            $listingProductTable,
            array('category_id' => null),
            array('category_id = ?' => $this->getId())
        );

        $this->deleteSpecifics();
        $this->getDescriptionTemplate()->deleteInstance();

        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            $attributeSet->deleteInstance();
        }

        $this->delete();

        $this->descriptionTemplateModel = NULL;

        return true;
    }

    // ########################################

    public function getAttributeSets()
    {
        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_AMAZON_CATEGORY);
        $collection->addFieldToFilter('object_id',(int)$this->getId());

        return $collection->getItems();
    }

    public function getAttributeSetsIds()
    {
        $ids = array();
        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            /** @var $attributeSet Ess_M2ePro_Model_AttributeSet */
            $ids[] = $attributeSet->getAttributeSetId();
        }

        return $ids;
    }

    // ########################################

    public function getSpecifics()
    {
        return Mage::getModel('M2ePro/Amazon_Category_Specific')
            ->getCollection()
            ->addFieldToFilter('category_id',$this->getId())
            ->getData();
    }

    public function deleteSpecifics()
    {
        $specifics = $this->getRelatedSimpleItems('Amazon_Category_Specific','category_id',true);
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Category_Description
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {
            $this->descriptionTemplateModel = Mage::getModel('M2ePro/Amazon_Category_Description')->loadInstance(
                $this->getData('category_description_id')
            );
        }

        return $this->descriptionTemplateModel;
    }

    // ########################################

    public function getCategoryPath()
    {
        return $this->getData('category_path');
    }

    public function getNodeTitle()
    {
        return $this->getData('node_title');
    }

    public function getCategoryIdentifiers()
    {
        $return = json_decode($this->getData('identifiers'),true);
        is_null($return) && $return = array('item_types' => null,'browsenode_id' => null);

        return $return;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Category_Source
    */
    public function getSource(Ess_M2ePro_Model_Amazon_Listing_Product $listingProduct)
    {
        return Mage::getModel(
            'M2ePro/Amazon_Category_Source',
            array(
                $listingProduct,
                $this
            )
        );
    }

    // ########################################

    public function map($listingProductIds)
    {
        if (count($listingProductIds) < 0) {
            return false;
        }

        $categoryAttributes = $this->getAttributeSetsIds();

        $listingAttributes = Mage::helper('M2ePro/Component_Amazon')
            ->getObject('Listing_Product',reset($listingProductIds))
            ->getListing()
            ->getAttributeSetsIds();

        foreach ($listingAttributes as $listingAttribute) {
            if (array_search($listingAttribute,$categoryAttributes) === false) {
                return false;
            }
        }

        foreach ($listingProductIds as $listingProductId) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product',$listingProductId);

            $generalId = $listingProductInstance->getChildObject()->getData('general_id');
            $generalIdSearchStatus = $listingProductInstance->getChildObject()->getData('general_id_search_status');

            if (!is_null($generalId) ||
                $generalIdSearchStatus == Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING){
                continue;
            }

            $listingProductInstance->getChildObject()->setData('category_id',$this->getId())->save();
        }

        return true;
    }

    // ########################################
}