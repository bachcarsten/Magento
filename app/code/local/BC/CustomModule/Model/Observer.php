<?php
class BC_CustomModule_Model_Observer
{
    public function orderPlaceAfter($observer){
        $order = $observer->getOrder();
        if(!$order->getShippingDescription()){
            switch($order->getShippingMethod()){
                case 'goodahead_flatrate_flatrate':
                    $order->setShippingDescription('Best Way Possible - Flat Rate');
                    break;
                case 'flatrate_flatrate':
                    $order->setShippingDescription('Pickup - Minneapolis Warehouse Pickup');
                    break;
                case 'matrixrate_matrixrate':
                    $order->setShippingDescription('SpeeDee Delivery - SpeeDee');
                    break;
                case 'matrixrate_matrixrate_free':
                    $order->setShippingDescription('SpeeDee Delivery - SpeeDee');
                    break;
                case 'ups_01':
                    $order->setShippingDescription('UPS - UPS Next Day Air');
                    break;
                case 'ups_02':
                    $order->setShippingDescription('UPS - UPS Second Day Air');
                    break;
                case 'ups_03':
                    $order->setShippingDescription('UPS - UPS Ground');
                    break;
                default:
            }
            $order->save();
        }
        
        if(!$order->getCustomerEmail()){
            $billingAddress = $order->getBillingAddress();
            $order->setCustomerId($billingAddress->getCustomerId());
            $order->setCustomerEmail($billingAddress->getEmail());
            $order->setCustomerFirstname($billingAddress->getFirstname());
            $order->setCustomerLastname($billingAddress->getLastname());
            $order->save();
        }
    }
    
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $shippingDesc = $order->getShippingDescription();
        
        //Mage::log(print_r($order->getIncrementId(), true), null, 'netsuite-shipment.log', true);
//        Mage::log(print_r($order->getStatus(), true), null, 'netsuite-shipment.log', true);
//        $shipment = $order->getShipmentsCollection()->getFirstItem();
//        $shipmentIncrementId = $shipment->getIncrementId();
//        Mage::log(print_r($shipmentIncrementId, true), null, 'netsuite-shipment.log', true);
        $_tracks = $shipment->getAllTracks();
        foreach ($_tracks as $_track) {
            //Mage::log(print_r($_track, true), null, 'netsuite-shipment.log', true);
            switch($_track->getTitle()){
                case '01':
                    $order->setShippingDescription('UPS - UPS Ground');
                    break;
                case '02':
                    $order->setShippingDescription('UPS - UPS Ground');
                    break;
                case '03':
                    $order->setShippingDescription('UPS - UPS Ground');
                    break;
                case 'matrixrate':
                    if ($shippingDesc != 'Pickup - Minneapolis Warehouse Pickup')
                        $order->setShippingDescription('SpeeDee Delivery - SpeeDee');
                    break;
                case 'flatrate':
                    $order->setShippingDescription('Best Way Possible - Flat Rate');
                    break;
            }
        }

        if($order->getStatus() == 'processing') {
            $order->setData('state', 'complete');
            $order->setStatus('complete');       
            $history = $order->addStatusHistoryComment('Order was set to Complete by our automation tool.', false);
            $history->setIsCustomerNotified(false);
        }
        
        $order->save();
    }
}
