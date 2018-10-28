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

            $outputArray = split("\|", $this->strDISPLAY);

            $menu = $outputArray[0];
            $_SESSION["NEXT_LEVEL"] = $outputArray[1];
            $_SESSION["EXTRA"] = $outputArray[2];
        } catch (Exception $ex) {
            $menu = "Sorry PayTrade Service is currently unavailable. Kindly Bare with us as we work on fixing this issue";
        }


        return $menu;
    }

    private function menuLevel0() {

        $this->strDISPLAY = "Welcome to PayTrade Mobile Service\n1.My Vouchers\n2.Buy a Voucher. |1|EXTRA";
    }

    private function menuLevel1() {
        if ($this->USSD_STRING == "1") {
            $this->menuLevel2();
        } else if ($this->USSD_STRING == "2") {
            $this->menuLevel14();
        } else {
            $this->menuLevel0();
        }
    }

    private function menuLevel2() {
        $this->strDISPLAY = "Select Voucher\n1.Gift Vourcher(Ksh.500).\n2.Shopping Voucher(Ksh.5000):|3|EXTRA";
    }

    private function menuLevel3() {
        $this->strDISPLAY = "Gift Vourcher(Ksh.500)\n1.Send to Someone\n2.Redeem Voucher\n3.View Bar Code|4|EXTRA";
    }

    private function menuLevel4() {
          if ($this->USSD_STRING == "1") {
            $this->menuLevel5();
        } else if ($this->USSD_STRING == "2") {
            $this->menuLevel8();
        } else if ($this->USSD_STRING == "3") {
            $this->menuLevel13();
        } else {
            $this->menuLevel0();
        }
    }

    private function menuLevel5() {
        $this->strDISPLAY = "Enter Receivers Mobile Number:|6|EXTRA";
    }

    private function menuLevel6() {
        $this->strDISPLAY = "Confirm Sending Gift Voucher(500) to $this->USSD_STRING.\n1.Confirm\n2.Cancel|7|$this->USSD_STRING";
    }

    private function menuLevel7() {//Get Application Status
        $this->strDISPLAY = "Gift Voucher(500) Has Been Sent to $this->EXTRA. They will receive the details on their phone. Call 02000230023 incase of any Issue|8|EXTRA";
    }

    private function menuLevel8() {
        $this->strDISPLAY = "Enter your Merchant's Number.\n0.Back|9|0";
    }

    private function menuLevel9() {//Check Land Rent
        $this->strDISPLAY = "Enter your Amount:|10|$this->USSD_STRING";
    }

    private function menuLevel10() {
        $this->strDISPLAY = "Confirm Redeeming of $this->INPUT from Gift Voucher(500) to Merchant $this->EXTRA |11|0";
    }
    
    private function menuLevel11() {
        $this->strDISPLAY = "Confirm Redeeming of $this->INPUT to Merchant ($this->EXTRA) |12|0";
    }
    
   private function menuLevel12() {
        $this->strDISPLAY = "Your Request is Being Processed. You will receive a confirmation shortly. |0|0";
    }
    
    private function menuLevel13() {
        $this->strDISPLAY = " Gift Voucher(500)- BarCode:- 345332354434 |0|0";
    }
    
    private function menuLevel14() {
        $this->strDISPLAY = "Service Coming Soon|0|0";
    }
     
    

}
