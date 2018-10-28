<?php

ini_set("soap.wsdl_cache_enabled", "0"); 


require 'noti-functions.php';

$server = new SoapServer("../wsdl/parlayx_sms_notification_service_2_2.wsdl");

$server->setClass("FunctionsClass");
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();

?>
