
<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); 
include_once './Core/pGateway.php';
$serviceID = $_REQUEST['serviceID'];
$payload = urldecode($_REQUEST['payload']);
echo pGateway::createInstance($serviceID, $payload)->performService();
?>
