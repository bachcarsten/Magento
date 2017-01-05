<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Category_Description extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    const TITLE_MODE_PRODUCT = 0;
    const TITLE_MODE_CUSTOM  = 1;

    const BRAND_MODE_NONE = 0;
    const BRAND_MODE_CUSTOM = 1;

    const MANUFACTURER_MODE_NONE = 0;
    const MANUFACTURER_MODE_CUSTOM = 1;

    const MANUFACTURER_PART_NUMBER_MODE_NONE = 0;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM = 1;

    const BULLET_POINTS_MODE_NONE   = 0;
    const BULLET_POINTS_MODE_CUSTOM = 1;

    const DESCRIPTION_MODE_NONE     = 0;
    const DESCRIPTION_MODE_PRODUCT  = 1;
    const DESCRIPTION_MODE_SHORT    = 2;
    const DESCRIPTION_MODE_CUSTOM   = 3;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Category_Description');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            $attributeSet->deleteInstance();
        }

        $this->delete();

        return true;
    }

    // ########################################

    public function getAttributeSets()
    {
        $temp = $this->getData('cache_attribute_sets');

        if (!empty($temp)) {
            return $temp;
        }

        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter(
            'object_type',
            Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_AMAZON_CATEGORY_DESCRIPTION
        );
        $collection->addFieldToFilter('object_id',(int)$this->getId());

        $this->setData('cache_attribute_sets',$collection->getItems());

        return $this->getData('cache_attribute_sets');
    }

    public function getAttributeSetsIds()
    {
        $temp = $this->getData('cache_attribute_sets_ids');

        if (!empty($temp)) {
            return $temp;
        }

        $ids = array();
        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            /** @var $attributeSet Ess_M2ePro_Model_AttributeSet */
            $ids[] = $attributeSet->getAttributeSetId();
        }

        $this->setData('cache_attribute_sets_ids',$ids);

        return $this->getData('cache_attribute_sets_ids');
    }

    // ########################################

    public function getTitleMode()
    {
        return (int)$this->getData('title_mode');
    }

    public function isTitleModeProduct()
    {
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT;
    }

    public function isTitleModeCustom()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM;
    }

    public function getTitleSource()
    {
        return array(
            'mode'     => $this->getTitleMode(),
            'template' => $this->getData('title_template')
        );
    }

    public function getTitleAttributes()
    {
        $attributes = array();
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBrandMode()
    {
        return (int)$this->getData('brand_mode');
    }

    public function isBrandModeNone()
    {
        return $this->getBrandMode() == self::BRAND_MODE_NONE;
    }

    public function isBrandModeCustom()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM;
    }

    public function getBrandSource()
    {
        return array(
            'mode'     => $this->getBrandMode(),
            'template' => $this->getData('brand_template')
        );
    }

    public function getBrandAttributes()
    {
        $attributes = array();
        $src = $this->getBrandSource();

        if ($src['mode'] == self::BRAND_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerMode()
    {
        return (int)$this->getData('manufacturer_mode');
    }

    public function isManufacturerModeNone()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_NONE;
    }

    public function isManufacturerModeCustom()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM;
    }

    public function getManufacturerSource()
    {
        return array(
            'mode'     => $this->getManufacturerMode(),
            'template' => $this->getData('manufacturer_template')
        );
    }

    public function getManufacturerAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerSource();

        if ($src['mode'] == self::MANUFACTURER_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerPartNumberMode()
    {
        return (int)$this->getData('manufacturer_part_number_mode');
    }

    public function isManufacturerPartNumberModeNone()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_NONE;
    }

    public function isManufacturerPartNumberModeCustom()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM;
    }

    public function getManufacturerPartNumberSource()
    {
        return array(
            'mode'     => $this->getManufacturerPartNumberMode(),
            'template' => $this->getData('manufacturer_part_number_template')
        );
    }

    public function getManufacturerPartNumberAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerPartNumberSource();

        if ($src['mode'] == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBulletPointsMode()
    {
        return (int)$this->getData('bullet_points_mode');
    }

    public function getBulletPointsTemplate()
    {
        return is_null($this->getData('bullet_points')) ? array() : json_decode($this->getData('bullet_points'),true);
    }

    public function isBulletPointsModeNone()
    {
        return $this->getBulletPointsMode() == self::BULLET_POINTS_MODE_NONE;
    }

    public function isBulletPointsModeCustom()
    {
        return $this->getBulletPointsMode() == self::BULLET_POINTS_MODE_CUSTOM;
    }

    public function getBulletPointsSource()
    {
        return array(
            'mode'     => $this->getBulletPointsMode(),
            'template' => $this->getBulletPointsTemplate()
        );
    }

    public function getBulletPointsAttributes()
    {
        $src = $this->getBulletPointsSource();

        if ($src['mode'] == self::BULLET_POINTS_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::BULLET_POINTS_MODE_CUSTOM) {
            $match = array();
            $bullets = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_]+?)#/', $bullets, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getDescriptionMode()
    {
        return (int)$this->getData('description_mode');
    }

    public function isDescriptionModeNone()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_NONE;
    }

    public function isDescriptionModeProduct()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT;
    }

    public function isDescriptionModeShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_SHORT;
    }

    public function isDescriptionModeCustom()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_CUSTOM;
    }

    public function getDescriptionSource()
    {
        return array(
            'mode'     => $this->getDescriptionMode(),
            'template' => $this->getData('description_template')
        );
    }

    public function getDescriptionAttributes()
    {
        $attributes = array();
        $src = $this->getDescriptionSource();

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_SHORT) {
            $attributes[] = 'short_description';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource()
    {
        return array(
            'mode'     => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getGalleryImagesMode()
    {
        return (int)$this->getData('gallery_images_mode');
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array();
    }

    // ########################################
}