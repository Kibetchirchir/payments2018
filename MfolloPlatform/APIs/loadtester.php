<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '../Utils/config.php';
include_once 'MySqlD4M.php';


class smsAPI{
    private  $MSISDN;
    private  $MESSAGE;
    private  $SOURCE;
  
    
    public function __construct()
    {
        $this->MSISDN="888888";
        $this->MESSAGE="Testing";
        $this->SOURCE ="22222";
        
    }

    
    public function logSMS()
    {
        $table = "messages";
        $values = array("MSISDN" => $this->MSISDN, "SOURCE" => $this->SOURCE, "MESSAGE" => $this->MESSAGE);
        $logged = D4M::createInstance("localhost", "root", "2014tasha", "LTM")->DoInsert($table, $values);
        return $logged;
    }
 
}
$smsApi = new smsAPI();

echo $smsApi->logSMS();





