<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '../Utils/config.php';
include_once '../Utils/MySqlD4M.php';


class mpesaIPN {

    private $TRXID;
    private $MSISDN;
    private $TRXREF;
    private $TRXACCOUNT;
    private $TRXTIME;
    private $TRXSENDER;
    private $TRXAMOUNT;
    private $PGID = 1;
    
    
    
    public function __construct() {
        $id = $_REQUEST["id"];
        $orig = $_REQUEST["orig"];
        $dest = $_REQUEST["dest"];
        $tstamp = $_REQUEST["tstamp"];
        $text = $_REQUEST["text"];
        $customer_id = $_REQUEST["customer_id"];
        $user = $_REQUEST["user"];
        $pass = $_REQUEST["pass"];
        $routemethod_id = $_REQUEST["routemethod_id"];
        $routemethod_name = $_REQUEST["routemethod_name"];
        $mpesa_code = $_REQUEST["mpesa_code"];
        $mpesa_acc = $_REQUEST["mpesa_acc"];
        $mpesa_msisdn = $_REQUEST["mpesa_msisdn"];
        $mpesa_trx_date = $_REQUEST["mpesa_trx_date"];
        $mpesa_trx_time = $_REQUEST["mpesa_trx_time"];
        $mpesa_amt = $_REQUEST["mpesa_amt"];
        $mpesa_sender = $_REQUEST["mpesa_sender"];
        
        $this->TRXID=$id;
        $this->MSISDN = $mpesa_msisdn;
        $this->TRXREF =$mpesa_code;
        $this->TRXACCOUNT = $mpesa_acc;
        $this->TRXSENDER =  $mpesa_sender;
        $this->TRXTIME = $tstamp;
        $this->TRXAMOUNT = $mpesa_amt;
    }
    
    public function logPayment()
    {
        $table = "paymentLogs";
        $values = array("trxID" => $this->TRXID, "MSISDN" => $this->MSISDN, "trxRefNo" => $this->TRXREF, "trxAccountNo" => $this->TRXACCOUNT, "trxTimeStamp" => $this->TRXTIME, "trxSenderName" => $this->TRXTIME, "amount" => $this->TRXAMOUNT,  "dateCreated" => date("Y-m-d H:i:s"), "status"=>"2", "paymentGatewayID"=> $this->PGID);
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        
        if ($logged > 0)
        {
           return "OK|Payment Received by ECITIZEN";
        }else {
            return "NOK|ECITIZEN Rejected this payment";
        }
  
    }
   
  

}

 $IPN = new mpesaIPN();
 echo  $IPN->logPayment();
