<?php

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

require 'sync-functions.php';

$server = new SoapServer("../wsdl/osg_data_sync_service_1_0.wsdl");
$server->setClass("FunctionsClass");
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();
?>
