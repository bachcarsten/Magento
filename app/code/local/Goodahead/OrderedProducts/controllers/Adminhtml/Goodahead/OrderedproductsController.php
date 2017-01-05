<?php

class Goodahead_OrderedProducts_Adminhtml_Goodahead_OrderedproductsController extends Mage_Adminhtml_Controller_Action
{
    public function gridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('product.grid')
            ->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }

    public function gridOnlyAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('product.grid')
            ->setProducts($this->getRequest()->getPost('products', null));
        $this->renderLayout();
    }
}

