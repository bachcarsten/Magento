<?php
/**
 * This file is part of Goodahead_Core extension
 *
 * This extension is supplied with every Goodahead extension and provide common
 * features, used by Goodahead extensions.
 *
 * Copyright (C) 2014 Goodahead Ltd. (http://www.goodahead.com)
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
 * @copyright  Copyright (c) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html
 */

class Goodahead_Core_Adminhtml_Goodahead_Core_System_VariableController extends Mage_Adminhtml_Controller_Action
{
    /**
     * WYSIWYG Plugin Action
     *
     */
    public function wysiwygPluginAction()
    {
        $variables = array();
        if ($this->_isSystemVariableActionAllowed()) {
            $customVariables = Mage::getModel('core/variable')->getVariablesOptionArray(true);
            $storeContactVariabls = Mage::getModel('core/source_email_variables')->toOptionArray(true);
            $variables = array($storeContactVariabls, $customVariables);
        }
        $variablesObject = new Varien_Object($variables);
        Mage::dispatchEvent('goodahead_core_wysiwyg_plugin_action_response_before', array(
            'variables'    => $variablesObject,
            'action'       => $this,
        ));
        $this->getResponse()->setBody(Zend_Json::encode($variablesObject->getData()));
    }

    /**
     * Check current user permission
     *
     * @return boolean
     */
    protected function _isSystemVariableActionAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/variable');
    }
}