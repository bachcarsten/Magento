<?php

class Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Abstract
    extends Mage_Adminhtml_Block_Template
{
    protected $_items = array();
    protected $_renderer;
    protected $_rendererBlock;

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('goodahead/pdf/items.phtml');
    }

    public function setItems($items)
    {
        if (!is_array($items)) {
            $items = array($items);
        }
        $this->_items = $items;
        return $this;
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function getItemHtml($item, $addNewPage = false)
    {
        if (!isset($this->_rendererBlock)) {
            $this->_rendererBlock = Mage::getBlockSingleton(
                'goodahead_pdfreport/adminhtml_report_sales_node_'.$this->_renderer);
        }
        if (isset($this->_rendererBlock)) {
            $this->_rendererBlock->setItem($item)->setAddNewPage($addNewPage);
            return $this->_rendererBlock->toHtml();
        } else {
            return '';
        }
    }
}