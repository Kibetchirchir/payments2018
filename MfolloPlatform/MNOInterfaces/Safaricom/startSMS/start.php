<?php
include_once '../../../Utils/global-var.php';
include_once '../../../Utils/config.php';
//$flogPath2 = "/var/www/logs/sdpsmstests.log";
$soap_options = array(
    'trace' => 1, // traces let us look at the actual SOAP messages later
    'exceptions' => 1);
// Make sure the PHP-Soap module is installed
//echo "[2]: Checking SoapClient exists\n";
if (!class_exists('SoapClient')) {
 //   myFlog($flogPath2, "You haven't installed the PHP-Soap module.");
    echo "Class does not exits";}
// we use the WSDL file to create a connection to the web service
// echo "[3]: Creating webservice connection to $wsdl\n\n";
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
//$client = new SoapClient("../wsdl/parlayx_sms_notification_manager_service_2_3.wsdl", $soap_options);
$client = new SoapClient("WSDL:http:// 41.90.0.132/SDP_SMS/parlayx_sms_notification_manager_service_2_3.wsdl", $soap_options);
$myheader = new stdClass();
$myheader->spId = "601147";
$myheader->spPassword = md5("601147Mwangombe#12320150308163045");//md5("601311Mobikash12320120907163045"); //"cf52eff25905595d148e97d3f7ed0145";
//$myheader->serviceId = "6011472000003174"; 
//$myheader->serviceId = "6011472000003172"; 
//$myheader->serviceId = "6011472000003173"; 


$myheader->timeStamp = "20150308163045";
$header = new SoapHeader('http://www.csapi.org/wsdl/parlayx/sms/notification_manager/v2_3/interface', 'RequestSOAPHeader', $myheader, false);
$client->__setSoapHeaders($header);
//myFlog($flogPath2, "In Client-Functions: " . $client->__getFunctions());
try {
    $request = new stdClass;
    //$request->requestIdentifier = "100001200";
    $sendrequest = new stdClass;
//    $sendrequest->addresses = "tel:254701748548";
//    $sendrequest->senderName = "705";
    $sendrequest->reference = new stdClass;
    $sendrequest->reference->endpoint = "http://10.54.6.17/MfolloPlatform/MNOInterfaces/Safaricom/notification/index.php";
    //$sendrequest->reference->endpoint = "http://41.57.96.94/SDP/notification/";
    $sendrequest->reference->interfaceName = "FunctionsClass";
    $sendrequest->reference->correlator="1000"; 
    $sendrequest->smsServiceActivationNumber = "994";
    //$result = $client->getSmsDeliveryStatus($request);    
   $client->startSmsNotification($sendrequest);
    echo $client->__getLastRequest();
    echo "\n";
    echo $client->__getLastResponse();
    //  echo $result;
    // myFlog($flogPath2, "Response: " . print_r($result, true));
} catch (SoapFault $fault) {
   // myFlog($flogPath2, "Error: " . $fault->faultcode . "-" . $fault->faultstring);
    echo "Call Failed Jaribu badaye" . (string)$fault;
}//end try catch
//myFlog($flogPath2, "Printing last Request " . $client->__getLastRequest());
//myFlog($flogPath2, "Printing lastResponse " . $client->__getLastResponse());
?>
