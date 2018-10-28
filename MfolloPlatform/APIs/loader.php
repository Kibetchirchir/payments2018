<?php
for($x=0;$x=100000;$x++)
{
$url="http://localhost/MfolloPlatform/APIs/loadtester.php?MSISDN=888888&SHORTCODE=222222&MESSAGE=$x";
$output= join('',file($url));

echo $output. "Pumped";

}
