<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Phoenix
 * @package    Mango_Loworderfee
 * @copyright  Copyright (c) 2008-2009 Andrej Sinicyn, Mik3e
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mango_Loworderfee_Block_Invoice_Totals_Loworderfee extends Mage_Core_Block_Abstract
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_invoice   = $parent->getInvoice();
        if($this->_invoice->getLoworderfee()>0){
            $store = $this->_invoice->getStore()->getId();
            $loworderfee = new Varien_Object();
            $loworderfee->setLabel(Mage::getStoreConfig('sales/minimum_order/low_order_fee_title', $store));
            $loworderfee->setValue($this->_invoice->getLoworderfee());
            $loworderfee->setBaseValue($this->_invoice->getBaseLoworderfee());
            $loworderfee->setLoworderfee('loworderfee');
                $parent->addTotalBefore($loworderfee,'tax');
        }
        return $this;
    }
}