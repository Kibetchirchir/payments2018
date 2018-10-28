<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '../Utils/config.php';
include_once '../Utils/MySqlD4M.php';


class smsAPI{
    private  $MSISDN;
    private  $MESSAGE;
    private  $SOURCE;
  
    
    public function __construct()
    {
        $this->MSISDN=$_REQUEST["MSISDN"];
        $this->MESSAGE=$_REQUEST["MESSAGE"];
        $this->SOURCE = $_REQUEST["SHORTCODE"];
        
    }

    
    public function logSMS()
    {
        $table = "outbound";
        $values = array("MSISDN" => $this->MSISDN, "source" => $this->SOURCE, "message" => $this->MESSAGE, "dateCreated" => date("Y-m-d H:i:s"));
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        return $logged;
    }
 
}
$smsApi = new smsAPI();

echo $smsApi->logSMS();





