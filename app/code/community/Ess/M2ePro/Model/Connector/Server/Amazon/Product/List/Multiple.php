<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Product_List_Multiple
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'list';
    }

    protected function getResponserModel()
    {
        return 'Amazon_Product_List_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function prepareListingsProducts($listingsProducts)
    {
        $tempListingsProducts = array();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isNotListed()) {

                // Parser hack -> Mage::helper('M2ePro')->__('Item is already on Amazon, or not available.');
                $this->addListingsProductsLogsMessage($listingProduct, 'Item is already on Amazon, or not available.',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action') ||
                $listingProduct->isLockedObject($this->getActionIdentifier().'_action')) {

                // ->__('Another action is being processed. Try again when the action is completed.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Another action is being processed. Try again when the action is completed.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            $addingSku = $listingProduct->getChildObject()->getSku();
            empty($addingSku) && $addingSku = $listingProduct->getChildObject()->getAddingSku();

            if (empty($addingSku)) {

                // Parser hack -> Mage::helper('M2ePro')->__('SKU is not provided. Please, check Listing settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'SKU is not provided. Please, check Listing settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            if (strlen($addingSku) > 40) {

                // Parser hack -> Mage::helper('M2ePro')->__('The length of sku must be less than 40 characters.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'The length of sku must be less than 40 characters.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            $tempListingsProducts[] = $listingProduct;
        }

        $tempListingsProducts2 = $this->checkOnlineSkuExistance($tempListingsProducts);

        $tempListingsProducts = array();

        $this->params['list_types'] = array();

        foreach ($tempListingsProducts2 as $listingProduct) {

            if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
                $listType = $this->getListTypeChangerUser($listingProduct);
            } else {
                $listType = $this->getListTypeChangerAutomatic($listingProduct);
            }

            if ($listType === false) {
                continue;
            }

            if (!$this->validateConditions($listingProduct)) {
                continue;
            }

            $this->params['list_types'][$listingProduct->getId()] = $listType;
            $tempListingsProducts[] = $listingProduct;
        }

        return $tempListingsProducts;
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = array();

        $requestData['items'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $productVariations = $listingProduct->getVariations(true);

            foreach ($productVariations as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $variation->deleteInstance();
            }

            $nativeData = Mage::getModel('M2ePro/Amazon_Connector_Product_Helper')
                                         ->getListRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        return $requestData;
    }

    // ########################################

    private function getListTypeChangerUser(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $generalId = $listingProduct->getChildObject()->getGeneralId();

        if (!empty($generalId)) {
            if (!$this->validateGeneralId($generalId)) {
                // ->__('ASIN/ISBN has a wrong format.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'ASIN/ISBN has a wrong format.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_GENERAL_ID;
        }

        $message  = Mage::helper('M2ePro')->__('You can list a product only with assigned ASIN. ');
        $message .= Mage::helper('M2ePro')->__('Please, use the Search ASIN tool:  ');
        $message .= Mage::helper('M2ePro')->__('press the icon in ASIN/ISBN column or choose ');
        $message .= Mage::helper('M2ePro')->__('appropriate command in the Actions dropdown. ');
        $message .= Mage::helper('M2ePro')->__('Assigned ASIN will be displayed in ASIN/ISBN column.');

        $this->addListingsProductsLogsMessage(
            $listingProduct,
            $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
        return false;

//        todo new asin template
//        $categoryId = $listingProduct->getChildObject()->getCategoryId();
//
//        if (empty($categoryId)) {
//            // ->__('ASIN/ISBN or New ASIN template is required.');
//            $this->addListingsProductsLogsMessage(
//                $listingProduct,
//                'ASIN/ISBN or New ASIN template is required.',
//                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
//                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
//            );
//            return false;
//        }
//
//        $worldWideId = $listingProduct->getChildObject()->getWorldWideId();
//
//        if (empty($worldWideId)) {
//            $worldWideId = $listingProduct->getChildObject()->getAddingWorldWideId();
//        }
//
//        if (empty($worldWideId) || !$this->validateWorldWideId($worldWideId)) {
//            // ->__('Valid EAN/UPC is required. Please check Channel Settings.');
//            $this->addListingsProductsLogsMessage(
//                $listingProduct,
//                'Valid EAN/UPC is required. Please check Channel Settings.',
//                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
//                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
//            );
//            return false;
//        }
//
//        if ($this->isWorldWideIdAlreadyExists($worldWideId,$listingProduct)) {
//            return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_WORLDWIDE_ID;
//        }
//
//        return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_CATEGORY;
    }

    //-----------------------------------------

    private function getListTypeChangerAutomatic(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $generalId = $listingProduct->getChildObject()->getGeneralId();

        if (empty($generalId)) {
            $generalId = $listingProduct->getChildObject()->getAddingGeneralId();
        }

        if (!empty($generalId)) {
            if (!$this->validateGeneralId($generalId)) {
                // ->__('ASIN/ISBN has a wrong format. Please check Channel Settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'ASIN/ISBN has a wrong format. Please check Channel Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
                return false;
            }
            return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_GENERAL_ID;
        }

        $worldWideId = $listingProduct->getChildObject()->getWorldWideId();

        if (empty($worldWideId)) {
            $worldWideId = $listingProduct->getChildObject()->getAddingWorldWideId();
        }

        if (empty($worldWideId) || !$this->validateWorldWideId($worldWideId)) {
            // ->__('ASIN or UPC/EAN is required. Please check Channel Settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct,
                'ASIN or UPC/EAN is required. Please check Channel Settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            return false;
        }

//        todo new asin template
        return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_WORLDWIDE_ID;

//        if ($this->isWorldWideIdAlreadyExists($worldWideId,$listingProduct)) {
//            return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_WORLDWIDE_ID;
//        }
//
//        $categoryId = $listingProduct->getChildObject()->getCategoryId();
//
//        if (empty($categoryId)) {
//            // ->__('ASIN/ISBN or New ASIN template is required.');
//            $this->addListingsProductsLogsMessage(
//                $listingProduct,
//                'ASIN/ISBN or New ASIN template is required.',
//                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
//                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
//            );
//            return false;
//        }
//
        return Ess_M2ePro_Model_Amazon_Connector_Product_Helper::LIST_TYPE_CATEGORY;
    }

    // ########################################

    private function validateGeneralId($generalId)
    {
        $isAsin = Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId);

        if (!$isAsin) {

            $isIsbn = Mage::helper('M2ePro/Component_Amazon')->isISBN($generalId);

            if (!$isIsbn) {
                return false;
            }
        }

        return true;
    }

    private function validateWorldWideId($worldWideId)
    {
        $isUpc = Mage::helper('M2ePro/Component_Amazon')->isUPC($worldWideId);

        if (!$isUpc) {

            $isEan = Mage::helper('M2ePro/Component_Amazon')->isEAN($worldWideId);

            if (!$isEan) {
                return false;
            }
        }

        return true;
    }

    private function validateConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $addingCondition = $listingProduct->getChildObject()->getCondition();
        $validConditions = $listingProduct->getGeneralTemplate()->getChildObject()->getConditionValues();

        if (empty($addingCondition) || !in_array($addingCondition,$validConditions)) {

            // ->__('Condition is invalid or missed. Please, check Listing and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Condition is invalid or missed. Please, check Listing and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingConditionNote = $listingProduct->getChildObject()->getConditionNote();

        if (is_null($addingConditionNote)) {

            // ->__('Condition note is invalid or missed. Please, check Listing and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Condition note is invalid or missed. Please, check Listing and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (!empty($addingConditionNote) && strlen($addingConditionNote) > 2000) {

            // ->__('The length of condition note must be less than 2000 characters.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'The length of condition note must be less than 2000 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        return true;
    }

    //-----------------------------------------

    private function isWorldWideIdAlreadyExists($worldwideId,Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $marketplaceObj = $listingProduct->getGeneralTemplate()->getMarketplace();
        $accountObj = $listingProduct->getGeneralTemplate()->getAccount();

        /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
        $results = $dispatcher->runManual($listingProduct,$worldwideId,$marketplaceObj,$accountObj);

        if (empty($results)) {
            return false;
        }

        return true;
    }

    //-----------------------------------------

    private function checkOnlineSkuExistance($listingProducts)
    {
        $result = array();
        $listingProductsPacks = array_chunk($listingProducts,20,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            foreach ($listingProductsPack as $key => $listingProduct) {
                $skus[$key] = $listingProduct->getChildObject()->getAddingSku();
            }

            try {

                /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Server_Amazon_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
                $response = $dispatcherObject->processVirtualAbstract('product','search','asinBySku',
                    array('items' => $skus),'items', $this->marketplace->getId(), $this->account->getId());

            } catch (Exception $exception) {

                Mage::helper('M2ePro/Exception')->process($exception,true);

                $this->addListingsLogsMessage(
                    reset($listingProductsPack), Mage::helper('M2ePro')->__($exception->getMessage()),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            foreach($response as $key => $value) {
                if ($value === false || empty($value['asin']) ) {
                    $result[] = $listingProductsPack[$key];
                } else {
                    $this->updateListingProduct($listingProductsPack[$key], $value['asin']);
                }
            }
        }

        return $result;
    }

    private function updateListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, $generalId)
    {
        $tempSku = $listingProduct->getChildObject()->getAddingSku();

        $data = array(
            'general_id' => $generalId,
            'is_isbn_general_id' => Ess_M2ePro_Helper_Component_Amazon::isISBN($generalId),
            'sku' => $tempSku,
            'existance_check_status' => Ess_M2ePro_Model_Amazon_Listing_Product::EXISTANCE_CHECK_STATUS_FOUND,
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );

        $listingProduct->addData($data)->save();

        $dataForAdd = array(
            'account_id' => $listingProduct->getListing()->getGeneralTemplate()->getAccountId(),
            'marketplace_id' => $listingProduct->getListing()->getGeneralTemplate()->getMarketplaceId(),
            'sku' => $tempSku,
            'product_id' => $listingProduct->getProductId(),
            'store_id' => $listingProduct->getListing()->getStoreId()
        );

        Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();

        $message = Mage::helper('M2ePro')->__('The product was found in your Amazon inventory and linked by SKU.');

        $this->addListingsProductsLogsMessage(
            $listingProduct, $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    // ########################################
}