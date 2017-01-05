<?php

abstract class Goodahead_PdfReport_Block_Adminhtml_Report_Sales_Node_Abstract
    extends Mage_Adminhtml_Block_Template
{
    protected $_defaultTotalModel = 'sales/order_pdf_total_default';

    protected function _construct()
    {
        parent::_construct();

    }

    public function getLogoImage($store = null)
    {
//        return "";
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'sales/store/logo/' . $image;
//            if (is_file($image)) {
//                $image = Zend_Pdf_Image::imageWithPath($image);
//                $page->drawImage($image, 25, 800, 125, 825);
                return $image;
//            }
        }
        return "";
        //return $page;
    }

    public function getStoreAddress($store = null)
    {
        return nl2br(Mage::getStoreConfig('sales/identity/address', $store));
    }

    protected function _sortTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }
        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }
        return ($a['sort_order'] > $b['sort_order']) ? 1 : -1;
    }

    protected function _getTotalsList($source)
    {
        $totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();
        usort($totals, array($this, '_sortTotalsList'));
        $totalModels = array();
        foreach ($totals as $index => $totalInfo) {
            if (!empty($totalInfo['model'])) {
                $totalModel = Mage::getModel($totalInfo['model']);
                if ($totalModel instanceof Mage_Sales_Model_Order_Pdf_Total_Default) {
                    $totalInfo['model'] = $totalModel;
                } else {
                    Mage::throwException(
                        Mage::helper('sales')->__('PDF total model should extend Mage_Sales_Model_Order_Pdf_Total_Default')
                    );
                }
            } else {
                $totalModel = Mage::getModel($this->_defaultTotalModel);
            }
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    public function getTotals()
    {
        $source = $this->getItem();
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $result = array();
        foreach ($totals as $total) {
            $total->setOrder($order)
                ->setSource($source);

            if ($total->canDisplay()) {
                foreach ($total->getTotalsForDisplay() as $totalData) {
//                    if ($totalData['amount'] > 0) {
                        $result[] = array(
                            'label'     => $totalData['label'],
                            'amount'    => $totalData['amount'],
                        );
//                    }
                }
            }
        }

        return $result;
    }

    abstract public function getOrder();

    public function getBillingAddress()
    {
    	return $this->getOrder()->getBillingAddress()->format('html');
    }

    public function getShippingAddress()
    {
        if (!$this->getOrder()->getIsVirtual()) {
            return $this->getOrder()->getShippingAddress()->format('html');
        } else {
            return "";
        }
    }

    public function getPaymentMethod()
    {
        $paymentInfo = Mage::helper('payment')->getInfoBlock($this->getOrder()->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value))==''){
                unset($payment[$key]);
            }
        }

        return implode("<br/>", $payment);
    }

    public function getShippingMethod()
    {
        if (!$this->getOrder()->getIsVirtual()) {
            $shippingMethod  = $this->getOrder()->getShippingDescription();
            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " " . $this->getOrder()->formatPriceTxt($this->getOrder()->getShippingAmount()) . ")";
            return $shippingMethod."<br/>".$totalShippingChargesText;
        } else {
            return "";
        }

    }
    //    abstract public function get

}