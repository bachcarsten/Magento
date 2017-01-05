<pre><?php

$proxy = new SoapClient('http://shop.intermixbev.com/api/v2_soap/?wsdl'); 
$sessionId = $proxy->login('OZLINK', 'Espresso23'); 
var_dump('sessionid: '.$sessionId);
$filter = array('filter' => array(array('key' => 'status', 'value' => 'processing')));

//$result = $proxy->salesOrderList($sessionId, $filter);
$result = $proxy->salesOrderInfo($sessionId, 100014504);

var_dump($result);
