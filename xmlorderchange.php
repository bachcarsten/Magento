<?php

$proxy = new SoapClient('http://shop.intermixbev.com/api/v2_soap/?wsdl'); 
$sessionId = $proxy->login('OZLINK', 'Espresso23'); 
var_dump('sessionid: '.$sessionId);

$orderId='100012740';
$orderStatus = 'complete';
$comment = 'The order was successfully shipped.';
$sendEmailToCustomer = true;

$result = $proxy->salesOrderAddComment($sessionId, $orderId, $orderStatus, $comment, $sendEmailToCustomer);

var_dump($result);
