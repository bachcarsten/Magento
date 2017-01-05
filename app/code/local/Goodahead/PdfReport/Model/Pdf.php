<?php
require_once('Dompdf/dompdf_config.inc.php');

class Goodahead_PdfReport_Model_Pdf extends Mage_Core_Model_Abstract
{
//    protected $_attachments = array();
    protected $_adapter;


    protected function _getPdfAdapter()
    {
        if (!isset($this->_adapter)) {
            $this->_adapter = new DOMPDF();
        }

        return $this->_adapter;
    }

    public function getPdf($html)
    {
        if ($html instanceof Mage_Core_Block_Abstract) {
            $html = $html->toHtml();
        }

        $this->_getPdfAdapter()->load_html($html);
        $this->_getPdfAdapter()->render($html);
        return $this->_getPdfAdapter()->output();
    }


}