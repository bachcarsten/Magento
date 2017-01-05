<?php
//$proxy = new SoapClient('http://shop.intermixbev.com/index.php/api/v2_soap?wsdl=1');
try {
$proxy = new SoapClient('http://shop.intermixbev.com/index.php/api/v2_soap?wsdl=1');
//$proxy = new SoapClient('http://local.magento/index.php/api/v2_soap?wsdl=1');
$sessionId = $proxy->login('OzLINK', 'OzLINK');
$complexFilter = array(
    'complex_filter' => array(
        array(
            'key' => 'type',
            'value' => array('key' => 'in', 'value' => 'simple,configurable')
        )
    )
);
$result = $proxy->catalogProductList($sessionId, $complexFilter);
echo '<pre>';
var_dump($result);exit;
} catch (Exception $e) {
echo $e.getMessage(); 
}

?>