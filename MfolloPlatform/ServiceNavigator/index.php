<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '/apps/MfolloPlatform/Utils/global-var.php';
$MSISDN = $_REQUEST['MSISDN'];
$USSD_STRING = $_REQUEST['USSD_STRING'];
$SERVICE_CODE = $_REQUEST['SERVICE_CODE'];
$SESSION_ID = $_REQUEST['SESSION_ID'];

  session_id($SESSION_ID);
  session_start();

$FLOGPATH = "/srv/log/Ussd_Info.log";

require_once 'navigator.php';

flog($FLOGPATH, "New Ussd Request ================= \n".print_r($_REQUEST, true));

$nav = new Navigator($MSISDN, $SERVICE_CODE, $USSD_STRING, $SESSION_ID);
echo $nav->processRequest();


