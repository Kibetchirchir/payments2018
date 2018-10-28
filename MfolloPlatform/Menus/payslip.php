<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Menu {

    protected $MSISDN;
    protected $USSD_STRING;
    protected $EXTRA;
    protected $NEXT_LEVEL;

    public final function __construct($MSISDN, $USSD_STRING) {
        $this->MSISDN = $MSISDN;
        $this->USSD_STRING = $USSD_STRING;
        
        if(isset($_SESSION['EXTRA']))
        {
        $this->EXTRA = $_SESSION['EXTRA'];
        $this->NEXT_LEVEL = $_SESSION['NEXT_LEVEL'];
        }else
        {
            $this->EXTRA="0";
            $this->NEXT_LEVEL ="0";
        }
    }

    public static function createInstance($MSISDN, $USSD_STRING) {
        $DMENU = new self($MSISDN, $USSD_STRING);
        return $DMENU;
    }

    public function processMenu() {
try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.ghris.go.ke/VAS/GHRIS/Default.ashx?MSISDN=$this->MSISDN&NEXT_LEVEL=$this->NEXT_LEVEL&USSD_STRING=$this->USSD_STRING&EXTRA=".rawurlencode($this->EXTRA));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch) . curl_error($ch);
        curl_close($ch);

        $outputArray = split("\|", $output);
        
        $menu = $outputArray[0];
        $_SESSION["NEXT_LEVEL"] = $outputArray[1];
        $_SESSION["EXTRA"] = $outputArray[2];
}catch(Exception $ex)
{
    $menu = "Sorry GHRIS Service is currently unavailable. Kindly Bare with us as we work on fixing this issue";
}


        return $menu;
    }

}
