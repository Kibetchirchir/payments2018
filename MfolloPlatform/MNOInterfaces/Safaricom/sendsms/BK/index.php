<?php
/*
 * Safaricom SDP Proxy Script
 * Author: Evid Araka Sibi
 * Extend this Code to your interface master if you are working in PHP.
 */

$SPID = $_REQUEST["SPID"];// Service provider ID as on Safaricom portal
$SERVICEID = $_REQUEST["SERVICEID"];//Service ID as on Safarom Portal
$MESSAGE = $_REQUEST["MESSAGE"];//
$SOURCE = $_REQUEST["SOURCE"];//Short Code
$DEST = $_REQUEST["DEST"];// MSISDN
$LINKID = $_REQUEST["LINKID"];// Required in case of On Demand
$SMSID = $_REQUEST["SMSID"]; // Unique ID Passed to Safaricom.

include_once '../../../Utils/global-var.php';
include_once '../../../Utils/config.php';

$soap_options = array(
    'trace' => 1, 
    'exceptions' => 1);

// Make sure the PHP-Soap module is installed
if (!class_exists('SoapClient')) {
  // myFlog($flogPath2, "You haven't installed the PHP-Soap module.");
    echo "Class does not exits";
}

// we use the WSDL file to create a connection to the web service
// echo "[3]: Creating webservice connection to $wsdl\n\n";
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$client = new SoapClient("../wsdl/parlayx_sms_send_service_2_2.wsdl", $soap_options);


$myheader = new stdClass();
$myheader->spId = "$SPID";
$myheader->spPassword = md5("$SPID".Props::$SAFARICOM_PASSWORD."20120907163045"); //"cf52eff25905595d148e97d3f7ed0145";
$myheader->serviceId = "$SERVICEID";
$myheader->timeStamp = "20120907163045";
//$myheader->linkid = "$LINKID";
$myheader->OA = "tel:$DEST";
$myheader->FA = "tel:$DEST";


$header = new SoapHeader('http://www.csapi.org/wsdl/parlayx/sms/send/v2_2/service', 'RequestSOAPHeader', $myheader, false);

$client->__setSoapHeaders($header);
//myFlog($flogPath2, "In Client-Functions: " . $client->__getFunctions());

try {
    $request = new stdClass;  
    $sendrequest = new stdClass;
    $sendrequest->addresses = "tel:$DEST";
    $sendrequest->senderName = "$SOURCE";
//    $sendrequest->charging = new stdClass;
//    $sendrequest->charging->description = "Payment information";
//    $sendrequest->charging->currency = "KSH";
//    $sendrequest->charging->amount = "300";
//    $sendrequest->charging->code = "MDSP2000203475";
    $sendrequest->message = $MESSAGE;
    $sendrequest->receiptRequest = new stdClass;
    $sendrequest->receiptRequest->endpoint = "http://197.232.26.3/MfolloPlatform/MNOInterfaces/Safaricom/notification/index.php";
    //$sendrequest->receiptRequest->endpoint = "http://41.57.96.94/SDP/notification/";
    $sendrequest->receiptRequest->interfaceName = "FunctionsClass";
    $sendrequest->receiptRequest->correlator="$SMSID"; 
      

 echo $client->__getLastRequest();
    
$result = $client->sendSms($sendrequest);
    
   echo $client->__getLastRequest();
   echo "\n";
    echo $client->__getLastResponse();
    
//   myFlog($flogPath2, "Response: " . print_r($result, true));
} catch (SoapFault $fault) {
   
//  myFlog($flogPath2, "Error: " . $fault->faultcode . "-" . $fault->faultstring);
    
    echo "Call Failed Jaribu badaye" . (string)$fault;
}

//myFlog($flogPath2, "Printing last Request " . $client->__getLastRequest());
//myFlog($flogPath2, "Printing lastResponse " . $client->__getLastResponse());
?>

