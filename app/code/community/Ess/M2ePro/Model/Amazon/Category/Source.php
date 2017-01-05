<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Category_Source
{
    /* @var $listingProduct Ess_M2ePro_Model_Amazon_Listing_Product */
    private $listingProduct = null;

    /* @var $category Ess_M2ePro_Model_Amazon_Category */
    private $category = null;

    /* @var $descriptionTemplate Ess_M2ePro_Model_Amazon_Category_Description */
    private $descriptionTemplate = null;

    // ########################################

    public function __construct($args)
    {
        list($this->listingProduct,$this->category) = $args;

        $this->descriptionTemplate = $this->category->getDescriptionTemplate();
    }

    // ########################################

    public function getProductData()
    {
        $arrayXml = array();
        foreach ($this->category->getSpecifics() as $specific) {

            $xpath = trim($specific['xpath'],'/');
            $xpathParts = explode('/',$xpath);

            $path = '';
            $isFirst = true;

            foreach ($xpathParts as $part) {
                list($tag,$index) = explode('-',$part);

                if (!$tag) {
                    continue;
                }

                $isFirst || $path .= '{"childNodes": ';
                $path .= "{\"$tag\": {\"$index\": ";
                $isFirst = false;
            }

            if ($specific['mode'] == 'none') {

                $path .= '[]';
                $path .= str_repeat('}',substr_count($path,'{'));

                $arrayXml = Mage::helper('M2ePro')->arrayReplaceRecursive(
                    $arrayXml,
                    json_decode($path,true)
                );

                continue;
            }

            $value = $specific['mode'] == 'custom_value'
                ? $specific['custom_value']
                : $this->listingProduct->getMagentoProduct()->getAttributeValue($specific['custom_attribute']);

            $specific['type'] == 'int' && $value = (int)$value;
            $specific['type'] == 'float' && $value = (float)$value;
            $specific['type'] == 'date_time' && $value = str_replace(' ','T',$value);

            $attributes = array();
            foreach (json_decode($specific['attributes'],1) as $i=>$attribute) {

                list($attributeName) = array_keys($attribute);

                $attributeData = $attribute[$attributeName];

                $attributeValue = $attributeData['mode'] == 'custom_value'
                    ? $attributeData['custom_value']
                    : $this->listingProduct->getMagentoProduct()->getAttributeValue($attributeData['custom_attribute']);

                $attributes[$i] = array(
                    'name' => str_replace(' ','',$attributeName),
                    'value' => $attributeValue,
                );
            }

            $attributes = json_encode($attributes);

            $path .= '%data%';
            $path .= str_repeat('}',substr_count($path,'{'));

            $path = str_replace(
                '%data%',
                "{\"value\": \"$value\",\"attributes\": $attributes}",
                $path
            );

            $arrayXml = Mage::helper('M2ePro')->arrayReplaceRecursive(
                $arrayXml,
                json_decode($path,true)
            );
        }

        return $arrayXml;
    }

    // ---------------------------------------

    public function getDescriptionData()
    {
        $descriptionData = array(
            'title' => $this->getTitle(),
            'brand' => $this->getBrand(),
            'description' => $this->getDescription(),
            'bullets' => $this->getBulletPoints(),
            'manufacturer' => $this->getManufacturer(),
            'manufacturer_part_number' => $this->getManufacturerPartNumber(),
        );

        $categoryIdentifiers = $this->category->getCategoryIdentifiers();

        $descriptionData['item_types'] = $categoryIdentifiers['item_types'];
        $descriptionData['browsenode_id'] = $categoryIdentifiers['browsenode_id'];

        return $descriptionData;
    }

    // ---------------------------------------

    public function getImagesData()
    {
        if ($this->descriptionTemplate->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        $mainImage = array($mainImage);

        $limitGalleryImages = $this->descriptionTemplate->getGalleryImagesMode();

        if ($limitGalleryImages <= 0) {
            return $mainImage;
        }

        $galleryImages = $this->listingProduct->getMagentoProduct()->getGalleryImagesLinks($limitGalleryImages+1);
        $galleryImages = array_unique($galleryImages);

        if (count($galleryImages) <= 0) {
            return $mainImage;
        }

        if (in_array($mainImage[0],$galleryImages)) {

            $tempGalleryImages = array();
            foreach ($galleryImages as $tempImage) {
                if ($mainImage[0] == $tempImage) {
                    continue;
                }

                $tempGalleryImages[] = $tempImage;
            }
            $galleryImages = $tempGalleryImages;
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);

        return array_merge($mainImage, $galleryImages);
    }

    // ########################################

    public function getTitle()
    {
        $src = $this->descriptionTemplate->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Category_Description::TITLE_MODE_PRODUCT:
                $title = $this->listingProduct->getMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_Amazon_Category_Description::TITLE_MODE_CUSTOM:
                $title = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate(
                    $src['template'],
                    $this->listingProduct->getMagentoProduct()->getProduct()
                );
                break;

            default:
                $title = $this->listingProduct->getMagentoProduct()->getName();
                break;
        }

        return $title;
    }

    public function getBrand()
    {
        $brand = '';
        $src = $this->descriptionTemplate->getBrandSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Category_Description::BRAND_MODE_CUSTOM) {
            $brand = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate(
                $src['template'],
                $this->listingProduct->getMagentoProduct()->getProduct()
            );
        }

        return $brand;
    }

    public function getDescription()
    {
        $src = $this->descriptionTemplate->getDescriptionSource();
        /* @var $templateProcessor Mage_Core_Model_Email_Template_Filter */
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Category_Description::DESCRIPTION_MODE_PRODUCT:
                $description = $this->listingProduct->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Amazon_Category_Description::DESCRIPTION_MODE_SHORT:
                $description = $this->listingProduct->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Amazon_Category_Description::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate(
                    $src['template'],
                    $this->listingProduct->getMagentoProduct()->getProduct()
                );
                break;

            default:
                $description = '';
                break;
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    public function getBulletPoints()
    {
        $bullets = array();

        $src = $this->descriptionTemplate->getBulletPointsSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Category_Description::BULLET_POINTS_MODE_CUSTOM) {

            foreach ($src['template'] as $bullet) {
                $bullets[] = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate(
                    $bullet,
                    $this->listingProduct->getMagentoProduct()->getProduct()
                );
            }
        }

        return $bullets;
    }

    public function getManufacturer()
    {
        $manufacturer = '';
        $src = $this->descriptionTemplate->getManufacturerSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Category_Description::MANUFACTURER_MODE_CUSTOM) {
            $manufacturer = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate(
                $src['template'],
                $this->listingProduct->getMagentoProduct()->getProduct());
        }

        return $manufacturer;
    }

    public function getManufacturerPartNumber()
    {
        $mfrPartNumber = '';
        $src = $this->descriptionTemplate->getManufacturerPartNumberSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Category_Description::MANUFACTURER_PART_NUMBER_MODE_CUSTOM) {
            $mfrPartNumber = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate(
                $src['template'],
                $this->listingProduct->getMagentoProduct()->getProduct());
        }

        return $mfrPartNumber;
    }

    // ########################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->descriptionTemplate->isImageMainModeProduct()) {
            $imageLink = $this->listingProduct->getMagentoProduct()->getImageLink('image');
        }

        if ($this->descriptionTemplate->isImageMainModeAttribute()) {
            $src = $this->descriptionTemplate->getImageMainSource();
            $imageLink = $this->listingProduct->getMagentoProduct()->getImageLink($src['attribute']);
        }

        return $imageLink;
    }

    // ########################################

}