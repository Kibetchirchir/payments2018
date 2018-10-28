<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '/apps/WalletGateway/Utils/config.php';
include_once 'pServices.php';

class pGateway extends pServices 
{
   PROTECTED $SERVICEID;
   PROTECTED $PAYLOAD = array(); 
   
   public final function __construct($serviceID=array(), $payload=array()) {
       $this->SERVICEID =$serviceID ;
       $this->PAYLOAD = split(',', $payload);
   }
   public static function createInstance($serviceID, $payload) {

        $DB = new self($serviceID, $payload);
        return $DB;
    }
   public function performService()
   {
       $result = "NOK";
       switch ($this->SERVICEID) {
           case Props::$PAYBILL:
              $result = $this->CashIn($this->PAYLOAD[0],$this->PAYLOAD[1],$this->PAYLOAD[2],$this->PAYLOAD[3],$this->PAYLOAD[4]);
              //$result = call_user_func_array(array($this, 'Paybill'), $this->PAYLOAD);
               break;
           case Props::$AIRTIME:
             $result =  call_user_func_array($this->BuyAirtime, $this->PAYLOAD);
               break;
           case Props::$CASHOUT:
              $result = call_user_func_array($this->CashOut, $this->PAYLOAD);
               break;
           case Props::$CASHIN:
               $result = $this->CashIn($this->PAYLOAD[0],$this->PAYLOAD[1],$this->PAYLOAD[2],$this->PAYLOAD[3],$this->PAYLOAD[4]); 
               //call_user_method_array('CashIn', $this, $this->PAYLOAD); //call_user_func_array($this->c, $this->PAYLOAD);
               break;
           case Props::$FUNDTRANSFER:
               $result = call_user_func_array($this->FundsTransfer, $this->PAYLOAD);
               break;
           case Props::$BALANCEINQUIRY:
                $result = call_user_func_array($this->BalanceInquiry, $this->PAYLOAD);
               break;
           default:
               $result = $this->eServices($this->PAYLOAD[0], $this->PAYLOAD[1], $this->PAYLOAD[2], $this->PAYLOAD[3], $this->PAYLOAD[4], $this->PAYLOAD[5], $this->PAYLOAD[6], $this->PAYLOAD[7], $this->SERVICEID);
               break;
       }
       return $result;
   }
   
}



