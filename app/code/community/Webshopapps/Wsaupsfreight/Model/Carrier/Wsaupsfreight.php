<?php
/* YRC Freight Shipping
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsaupsfreight
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Wsaupsfreight_Model_Carrier_Wsaupsfreight
    extends Webshopapps_Wsafreightcommon_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'wsaupsfreight';

    protected $_modName = 'Webshopapps_Wsaupsfreight';

    protected $_prodGatewayUrl = 'https://onlinetools.ups.com/webservices/FreightRate';
    protected $_testGatewayUrl = 'https://wwwcie.ups.com/webservices/FreightRate';

   	public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
    	$r = $this->setBaseRequest($request);

        $r->setUserId($this->getConfigData('userid'));
        $r->setPassword($this->getConfigData('password'));
        $r->setBusRole('Shipper');
        $r->setPaymentTerms($this->getConfigData('payment_terms'));
        $r->setContainer($this->getConfigData('container'));

        $r->setPayorName($this->getConfigData('payor_name'));

      	if ($request->getPayorCountry()) {
            $payorCountry = $request->getPayorCountry();
        } else {
            $payorCountry = $this->getConfigData('payor_country_id');
        }

        $r->setPayorCountry(Mage::getModel('directory/country')->load($payorCountry)->getIso2Code());

        if ($request->getPayorRegionCode()) {
            $payorRegionCode = $request->getPayorRegionCode();
        } else {
            $payorRegionCode = $this->getConfigData('payor_region_id');
            if (is_numeric($payorRegionCode)) {
                $payorRegionCode = Mage::getModel('directory/region')->load($payorRegionCode)->getCode();
            }
        }
        $r->setPayorRegionCode($payorRegionCode);

        if ($request->getPayorPostcode()) {
            $r->setPayorPostal($request->getPayorPostcode());
        } else {
            $r->setPayorPostal($this->getConfigData('payor_postcode'));
        }

        if ($request->getPayorCity()) {
            $r->setPayorCity($request->getPayorCity());
        } else {
            $r->setPayorCity($this->getConfigData('payor_city'));
        }

        if ($request->getPayorStreetAddress()) {
            $r->setPayorStreetAddress($request->getPayorStreetAddress());
        } else {
            $r->setPayorStreetAddress($this->getConfigData('payor_street_address'));
        }

    	if ($request->getUpsUnitMeasure()) {
            $unit = $request->getUpsUnitMeasure();
        } else {
            $unit = $this->getConfigData('unit_of_measure');
        }

        $r->setUnitMeasure($unit);

        $this->_rawRequest = $r;

        return $this;
    }

   protected function _getQuotes()
   {
        $r = $this->_rawRequest;
        $userId = $this->getConfigData('userid');
	 	$password = $this->getConfigData('password');
	 	$shipCode = $this->getConfigData('access_license_number');
	 	$testMode = $this->getConfigData('test_mode');

        $xmlRequest = <<< XMLHeader
<env:Envelope xmlns:auth="http://www.ups.com/schema/xpci/1.0/auth"
xmlns:upss="http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0"
xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:common="http://www.ups.com/XMLSchema/XOLTWS/Common/v1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:fr="http://www.ups.com/XMLSchema/XOLTWS/FreightRate/v1.0"
xmlns:wsf="http://www.ups.com/schema/wsf">
<env:Header>
<upss:UPSSecurity>
<upss:UsernameToken>
XMLHeader;

        $xmlRequest .= "<upss:Username>{$userId}</upss:Username>";
        $xmlRequest .= "<upss:Password>$password</upss:Password></upss:UsernameToken><upss:ServiceAccessToken>";
        $xmlRequest .= "<upss:AccessLicenseNumber>$shipCode</upss:AccessLicenseNumber>";

        $xmlRequest .= <<< XMLAuth
</upss:ServiceAccessToken>
</upss:UPSSecurity>
</env:Header>
XMLAuth;

$xmlRequest .= <<< XMLRequest
<env:Body>
<fr:FreightRateRequest>
<common:Request/>
XMLRequest;
    if($testMode){
$xmlRequest .= <<< XMLRequest
	  <fr:ShipFrom>
		<fr:Address>
		  <fr:Name>Developer Test 1</fr:Name>
		  <fr:AddressLine>101 Developer Way</fr:AddressLine>
		  <fr:City>Richmond</fr:City>
          <fr:PostalCode>23224</fr:PostalCode>
          <fr:CountryCode>US</fr:CountryCode>
          <fr:StateProvinceCode>VA</fr:StateProvinceCode>
		</fr:Address>
      </fr:ShipFrom>
      <fr:ShipTo>
    	<fr:Address>
      	  <fr:Name>Consignee Test 1</fr:Name>
	  	  <fr:AddressLine>1000 Consignee Street</fr:AddressLine>
		  <fr:City>Allanton</fr:City>
          <fr:PostalCode>63001</fr:PostalCode>
          <fr:CountryCode>US</fr:CountryCode>
          <fr:StateProvinceCode>MO</fr:StateProvinceCode>
      	</fr:Address>
      </fr:ShipTo>
XMLRequest;
    } else {
$xmlRequest .= <<< XMLRequest
      	<fr:ShipFrom>
	      <fr:Address>
	        <fr:PostalCode>{$r->getOrigPostal()}</fr:PostalCode>
	        <fr:CountryCode>{$r->getOrigCountry()}</fr:CountryCode>
	        <fr:StateProvinceCode>{$r->getOrigRegionCode()}</fr:StateProvinceCode>
	      </fr:Address>
	    </fr:ShipFrom>
      	<fr:ShipTo>
      	  <fr:Address>
      		<fr:PostalCode>{$r->getDestPostal()}</fr:PostalCode>
      		<fr:CountryCode>{$r->getDestCountry()}</fr:CountryCode>
      		<fr:StateProvinceCode>{$r->getDestRegionCode()}</fr:StateProvinceCode>
      	  </fr:Address>
        </fr:ShipTo>
XMLRequest;
    }
$xmlRequest .= <<< XMLRequest
      <fr:PaymentInformation>
      <fr:Payer>
          <fr:Name>{$r->getPayorName()}</fr:Name>
      	  <fr:Address>
          	<fr:AddressLine>{$r->getPayorStreetAddress()}</fr:AddressLine>
          	<fr:City>{$r->getPayorCity()}</fr:City>
          	<fr:StateProvinceCode>{$r->getPayorRegionCode()}</fr:StateProvinceCode>
          	<fr:PostalCode>{$r->getPayorPostal()}</fr:PostalCode>
          	<fr:CountryCode>{$r->getPayorCountry()}</fr:CountryCode>
      	  </fr:Address>
XMLRequest;

        if ($shipper = $this->getConfigData('shipper_number') ) {
            $xmlRequest .= "<ShipperNumber>{$shipper}</ShipperNumber>";
        }
    if($testMode) {
$xmlRequest .= <<< XMLRequest
      	  </fr:Payer>
      	  <fr:ShipmentBillingOption>
      	  	<fr:Code>10</fr:Code>
      	  </fr:ShipmentBillingOption>
      	</fr:PaymentInformation>
      	<fr:Service>
      	  	<fr:Code>{$r->getAllowedMethods()}</fr:Code>
      	</fr:Service>
XMLRequest;
    } else {
$xmlRequest .= <<< XMLRequest
      	  </fr:Payer>
      	  <fr:ShipmentBillingOption>
      	  	<fr:Code>{$r->getPaymentTerms()}</fr:Code>
      	  </fr:ShipmentBillingOption>
      	</fr:PaymentInformation>
      	<fr:Service>
      	  	<fr:Code>{$r->getAllowedMethods()}</fr:Code>
      	</fr:Service>
XMLRequest;
    }
        $xmlRequest .= <<< XMLRequest
        <fr:HandlingUnitOne>
						<fr:Quantity>1</fr:Quantity>
						<fr:Type>
							<fr:Code>PLT</fr:Code>
						</fr:Type>
					</fr:HandlingUnitOne>
XMLRequest;

if(!$testMode){
    foreach ($this->getLineItems($r->getIgnoreFreeItems()) as $class=>$weight) {
    	$unit = $r->getUnitMeasure();
        if ($r->getUnitMeasure()=='CONVERT_LBS_KGS') {
        	$weight = $weight*0.4536;
            $unit = 'KGS';
        }


$xmlRequest .= <<< COMMODITY
	<fr:Commodity>
		<fr:Description>dummy desc</fr:Description>
		<fr:Weight>
			<fr:Value>{$weight}</fr:Value>
			<fr:UnitOfMeasurement>
				<fr:Code>{$unit}</fr:Code>
			</fr:UnitOfMeasurement>
		</fr:Weight>
		<fr:PackagingType>
      	  	<fr:Code>{$r->getContainer()}</fr:Code>
		</fr:PackagingType>
		<fr:FreightClass>{$class}</fr:FreightClass>
		<fr:NumberOfPieces>1</fr:NumberOfPieces>
	</fr:Commodity>
COMMODITY;
		}
} else {
$xmlRequest .= <<< COMMODITY
	<fr:Commodity>
		<fr:Description>dummy desc</fr:Description>
		<fr:Weight>
			<fr:Value>1500</fr:Value>
			<fr:UnitOfMeasurement>
				<fr:Code>LBS</fr:Code>
			</fr:UnitOfMeasurement>
		</fr:Weight>
		<fr:PackagingType>
      	  	<fr:Code>{$r->getContainer()}</fr:Code>
		</fr:PackagingType>
		<fr:FreightClass>92.5</fr:FreightClass>
		<fr:NumberOfPieces>1</fr:NumberOfPieces>
	</fr:Commodity>
COMMODITY;
}


		if (  $r->getOriginLiftgateReqd() ||  $r->getOriginResidential()) {
			$found = false;
			if ($r->getOriginResidential()) {
				if (!$found) {
					$xmlRequest .= "<fr:ShipmentServiceOptions><fr:PickupOptions>";
				}
				$xmlRequest .= "<fr:ResidentialPickupIndicator/>";
				$found = true;
			}
			if ($r->getOriginLiftgateReqd()) {
				if (!$found) {
					$xmlRequest .= "<fr:ShipmentServiceOptions><fr:PickupOptions>";
				}
				$xmlRequest .= "<fr:LiftGateRequiredIndicator/>";
				$found = true;
			}
			if ($found) {
				$xmlRequest .= "</fr:PickupOptions></fr:ShipmentServiceOptions>";
			}
		}


		if (Mage::helper('wsafreightcommon')->getUseLiveAccessories() && !$testMode) {
        	$accArray=$r->getAccessories();
        	$found=false;
			foreach ($accArray as $acc) { // Add accessorials to the XML Request
				switch ($acc) {
					case 'RES':
						if (!$found) {
							$xmlRequest .= "<fr:ShipmentServiceOptions><fr:DeliveryOptions>";
						}
						$xmlRequest .= "<fr:ResidentialDeliveryIndicator/>";
						$found = true;
						break;
					case 'LIFT':
						if (!$found) {
							$xmlRequest .= "<fr:ShipmentServiceOptions><fr:DeliveryOptions>";
						}
						$xmlRequest .= "<fr:LiftGateRequiredIndicator/>";
						$found = true;
						break;
				}
			}
			if ($found) {
				$xmlRequest .= "</fr:DeliveryOptions></fr:ShipmentServiceOptions>";
			}
		}
	 	$xmlRequest .= "</fr:FreightRateRequest></env:Body></env:Envelope>";


        if ($this->_debug) {
       		Mage::helper('wsacommon/log')->postNotice('wsaupsfreight','UPS LTL Request',$xmlRequest);
        }

		try {
        	    	$url = $this->getConfigData('gateway_url');
            		if ($url==='TEST') {
            			$url = $this->_testGatewayUrl;
            		} else {
                		$url = $this->_prodGatewayUrl;
            		}

            		    $header = array();
  			$header[] = "Content-Type: text/xml";
			$header[] = 'SOAPAction: "https://onlinetools.ups.com/webservices/FreightRateBinding"';
			$header[] = "Connection: close";
		            $ch = curl_init();
		            curl_setopt($ch, CURLOPT_URL, $url);
		            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
		           	curl_setopt($ch, CURLOPT_HEADER, 0);
		            curl_setopt($ch, CURLOPT_POST, 1);
		            curl_setopt($ch, CURLOPT_POSTFIELDS, "$xmlRequest");
		            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		            curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		            $xmlResponse = curl_exec ($ch);
		        } catch (Exception $e) {
		        	Mage::log($e->getMessage());
		            $xmlResponse = '';
		        }
      	if ($this->_debug) {
       		Mage::helper('wsacommon/log')->postNotice('wsaupsfreight','UPS LTL Response',$xmlResponse);
      	}

      	return $this->_parseDimXmlResponse($xmlRequest,$xmlResponse);
    }

  	public function isStateProvinceRequired()
    {
        return true;
    }

    protected function _parseDimXmlResponse($xmlRequest,$response)
    {
    	$costArr = array();
        $priceArr = array();
	    $quoteId='';
        if (strlen(trim($response))>0) {
    		if (Mage::helper('wsacommon')->
                    		checkItems('Y2FycmllcnMvd3NhdXBzZnJlaWdodC9zaGlwX29uY2U=',
								'b25zaWRl','Y2FycmllcnMvd3NhdXBzZnJlaWdodC9zZXJpYWw=')) {
                if (preg_match('#<\?xml version="1.0"\?>#', $response)) {
                    $response = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="ISO-8859-1"?>', $response);
                }
				$response = str_replace(':', '_', $response);
                $xml = simplexml_load_string($response);
				if (is_object($xml)) {
                	if (is_object($xml->soapenv_Body->Fault) && is_object($xml->soapenv_Body->Fault->Reason) &&
	                    (string)$xml->soapenv_Body->Fault->Reason!='') {
	               		$errorTitle = (string)$xml->soapenv_Body->Fault->Reason;
	                }	else {
	                 	if (is_object($xml->soapenv_Body->freightRate_FreightRateResponse) &&
	                 		is_object($xml->soapenv_Body->freightRate_FreightRateResponse->freightRate_TotalShipmentCharge) &&
	                  		floatval($xml->soapenv_Body->freightRate_FreightRateResponse->freightRate_TotalShipmentCharge->freightRate_MonetaryValue)!='') {

	                  		$charge = floatval($xml->soapenv_Body->freightRate_FreightRateResponse->freightRate_TotalShipmentCharge->freightRate_MonetaryValue);

                            if($charge <= 0) {
                                if(!Mage::helper('wsafreightcommon')->allowFreeFreight('wsaupsfreight')) {
                                    return $this->getResultSet($priceArr,$xmlRequest,$response,$quoteId);
                                }
                            }

							$code=$this->getConfigData('allowed_methods');
	                        $costArr[$code] = $charge;
	                        $priceArr[$code] = $this->getMethodPrice($charge, $code);

	                    }
	                }
                } else {
                	$errorTitle = 'Response is in the wrong format';
                }
            } else {
                $errorTitle = 'Response is in the wrong format';
            }
            if(!empty($errorTitle)){
                Mage::helper('wsacommon/log')->postNotice('wsaupsfreight','UPS Failed to Obtain Rate',$errorTitle);
            }
        }
        return $this->getResultSet($priceArr,$xmlRequest,$response,$quoteId);
    }

	public function getCode($type, $code='')
    {
        $codes = array(
            'method'=>array(
                '308'    => Mage::helper('usa')->__('LTL'),
                '309'    => Mage::helper('usa')->__('LTL - Guaranteed'),
        	),
           'payment'=>array(
                '10'   => Mage::helper('usa')->__('Prepaid'),
                '30'   => Mage::helper('usa')->__('Bill To Third Party'),
                '40'   => Mage::helper('usa')->__('Freight Collect'),
           ),
            'gateway_url'=>array(
                'TEST' 		=> Mage::helper('usa')->__('Test'),
                'LIVE'      => Mage::helper('usa')->__('Live'),
            ),
            'container'=>array(
            	'BAG'		=> Mage::helper('usa')->__('Bag'),
                'BOX'		=> Mage::helper('usa')->__('Box'),
            	'PLT'		=> Mage::helper('usa')->__('Pallet'),
                'REL'		=> Mage::helper('usa')->__('Reel'),
                'LOO'		=> Mage::helper('usa')->__('Loose'),
    		),
    		'unit_of_measure'=>array(
                'LBS'               =>  Mage::helper('usa')->__('Pounds'),
                'KGS'               =>  Mage::helper('usa')->__('Kilograms'),
                'CONVERT_LBS_KGS'   =>  Mage::helper('usa')->__('Convert LBS to KG'),
    		),
        );

        if (!isset($codes[$type])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code type: %s', $type));
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code for type %s: %s', $type, $code));
            return false;
        } else {
            return $codes[$type][$code];
        }
    }




}
