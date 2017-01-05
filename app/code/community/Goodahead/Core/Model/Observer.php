<?php
/**
 * This file is part of Goodahead_Core extension
 *
 * This extension is supplied with every Goodahead extension and provide common
 * features, used by Goodahead extensions.
 *
 * Copyright (C) 2013 Goodahead Ltd. (http://www.goodahead.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * and GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   Goodahead
 * @package    Goodahead_Core
 * @copyright  Copyright (c) 2013 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html
 */

class Goodahead_Core_Model_Observer
{
    public function renderMenu($observer)
    {
        /** @var $menu Varien_Simplexml_Element */
        $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
        foreach ($menu->xpath('//*[@update]') as $node) {
            $helperName = explode('/', (string)$node->getAttribute('update'));
            $helperMethod = array_pop($helperName);
            $helperName = implode('/', $helperName);
            $helper = Mage::helper($helperName);
            if ($helper && method_exists($helper, $helperMethod)) {
                $helper->$helperMethod($node);
            }
        }
    }

    public function updateMenuBlockCacheId($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Page_Menu) {
            /** @var $menu Varien_Simplexml_Element */
            $menu = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
            $additionalCacheKeyInfo = $block->getAdditionalCacheKeyInfo();
            if (!is_array($additionalCacheKeyInfo)) {
                $additionalCacheKeyInfo = array();
            }
            $additionalCacheKeyInfo['goodahead_core_cache_key_info'] = md5($menu->asXML());
            $block->setAdditionalCacheKeyInfo($additionalCacheKeyInfo);
        }
    }

    public function cmsPageSaveAfter($observer)
    {
        $object = $observer->getEvent()->getDataObject();
        if ($object instanceof Mage_Cms_Model_Page) {
            if (Mage::registry('goodahead_core_cms_update') !== true) {
                try {
                    $updateObject = Mage::getModel('goodahead_core/cms_update');
                    $updateObject->loadByObject($object);
                    if ($updateObject->getId()) {
                        $updateObject->setHasLocalChanges(1);
                        $updateObject->save();
                    }
                } catch (Exception $e) {

                }
            }
        }
    }

    public function cmsBlockSaveAfter($observer)
    {
        $object = $observer->getEvent()->getDataObject();

        if ($object instanceof Mage_Cms_Model_Block) {
            if (Mage::registry('goodahead_core_cms_update') !== true) {
                try {
                    $updateObject = Mage::getModel('goodahead_core/cms_update');
                    $updateObject->loadByObject($object);
                    if ($updateObject->getId()) {
                        $updateObject->setHasLocalChanges(1);
                        $updateObject->save();
                    }
                } catch (Exception $e) {

                }
            }
        }
    }
}