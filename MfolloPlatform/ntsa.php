<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '/apps/MfolloPlatform/Utils/config.php';
require_once '/apps/MfolloPlatform/Utils/MySqlD4M.php';
include_once '/apps/MfolloPlatform/Utils/global-var.php';
class Menu {

    protected $MSISDN;
    protected $USSD_STRING;
    protected $EXTRA;
    protected $NEXT_LEVEL;
    protected $strDISPLAY;

    public final function __construct($MSISDN, $USSD_STRING) {
        $this->MSISDN = $MSISDN;
        $this->USSD_STRING = $USSD_STRING;

        if (isset($_SESSION['NEXT_LEVEL'])) {
            $this->EXTRA = $_SESSION['EXTRA'];
            $this->NEXT_LEVEL = $_SESSION['NEXT_LEVEL'];
        } else {
            $this->EXTRA = "0";
            $this->NEXT_LEVEL = "0";
        }
    }

    public static function createInstance($MSISDN, $USSD_STRING) {
        $DMENU = new self($MSISDN, $USSD_STRING);
        return $DMENU;
    }

    public function processMenu() {
        try {
            /* Deprecated Code (Redirects the menu via HTTP to another server.)

              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, "https://www.ghris.go.ke/VAS/LAND/Navigator.ashx?MSISDN=$this->MSISDN&NEXT_LEVEL=$this->NEXT_LEVEL&USSD_STRING=$this->USSD_STRING&EXTRA=" . rawurlencode($this->EXTRA));
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              $output = curl_exec($ch) . curl_error($ch);
              curl_close($ch);

             */
            $nav = "menuLevel" . "$this->NEXT_LEVEL";

            $this->$nav();

            $outputArray = split("\\^", $this->strDISPLAY);

            $menu = $outputArray[0];
            $_SESSION["NEXT_LEVEL"] = $outputArray[1];
            $_SESSION["EXTRA"] = $outputArray[2];
        } catch (Exception $ex) {
            $menu = "Sorry NTSA Mobile Service is currently unavailable. Kindly Bare with us as we work on fixing this issue";
        }


        return $menu;
    }

    private function menuLevel0() {

        $this->strDISPLAY = "Welcome to NTSA Mobile Service.Enter Driving Licence Ref Number OR ID Number.^1^EXTRA";
    }

    private function menuLevel1() {

        $display = "";
        $Name = "";
        $IDNo = "";
        $Expiry = "";
        $Classes="";
        $Ref="";
        $strLicense="";
        
        $username = "evidsibi@gmail.com";
        $password = "mfollo'z";
        $RequestArray =array();
        if(strlen( strtoupper($this->USSD_STRING)) <= 6)
        {
        $RequestArray = array("c68" => strtoupper($this->USSD_STRING));
        }else {
            
         $RequestArray = array("c28" => strtoupper($this->USSD_STRING));
   
        }
//print($this->USSD_STRING."\n");
        $JsonRequest = json_encode($RequestArray);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://54.195.217.120/api/record-sets/2/records?query=".rawurlencode($JsonRequest));
        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "query=".rawurlencode($JsonRequest));
//curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $output = curl_exec($ch) . curl_error($ch);
        //  echo curl_error($curl);
        curl_close($ch);

        $ResponseArray = json_decode($output);
        
        $DetailsArray = $ResponseArray->records;
       // print_r($ResponseArray);
        if ($ResponseArray->count == "1") {
            
            $Name = $DetailsArray[0]->c2 . " " . $DetailsArray[0]->c12;
            $IDNo = $DetailsArray[0]->c28;
            $Expiry = $DetailsArray[0]->c64;
            $Classes= implode(',', $DetailsArray[0]->c72);
            $Ref= $DetailsArray[0]->c68;
            $strLicense = "Results for License Search: NAME:[$Name] IDNO:[$IDNo] EXPIRES:[$Expiry] CLASSES:[$Classes] FILE REF:[$Ref]";
            
            $display = "Details for Search: $this->USSD_STRING.\nNAME:$Name\nIDNO:$IDNo\nEXPIRES:$Expiry\nCLASSES:($Classes)\nREF:$Ref\n0.Back^0^EXTRA";
        } else {
             $strLicense ="No Valid Record was Found for License Search of $this->USSD_STRING";
             
            $display = "Details for Search: $this->USSD_STRING. No Valid Record was Found. \n0.Back ^0^EXTRA";
        }
        $this->logSMS($strLicense);
        
        $this->strDISPLAY = $display;
    }
    
    
     public function logSMS($message)
    {
         $sc='22430';
        $table = "outbound";
        $values = array("MSISDN" => $this->MSISDN, "source" => $sc, "message" => $message, "dateCreated" => date("h:i:sa"));
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        return $logged;
    }

}
