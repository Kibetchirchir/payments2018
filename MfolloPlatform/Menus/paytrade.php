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

        $this->strDISPLAY = "Welcome to Voucher Mobile Service\n1.My Vouchers\n2.Buy Vouchers\n3.View Balance\n4.Statements\n5.Pay Merchants|1|EXTRA";
    }

    private function menuLevel1() {
        if ($this->USSD_STRING == "1") {
            $this->menuLevel2();
        } else if ($this->USSD_STRING == "2") {
            $this->menuLevel8();
        } else if ($this->USSD_STRING == "3") {
            $this->menuLevel14();
        } else if ($this->USSD_STRING == "4") {
            $this->menuLevel15();
        } else if ($this->USSD_STRING == "5") {
            $this->menuLevel16();
        } else {
            $this->menuLevel0();
        }
    }

    private function menuLevel2() {
        $this->strDISPLAY = "Select Action:\n1.View Barcodes\n2.Redeem Airtime\n3.Redeem Pay Points|3|EXTRA";
    }

    private function menuLevel3() {
        if ($this->USSD_STRING == "1") {
            $this->menuLevel4();
        } else if ($this->USSD_STRING == "2") {
            $this->menuLevel5();
        } else if ($this->USSD_STRING == "3") {
            $this->menuLevel5();
        } else {
            $this->menuLevel0();
        }
    }

    private function menuLevel4() {
        $this->strDISPLAY = "Voucher Bar Codes:\nGift Voucher(500)- BarCode:- 345332354434\nGift Voucher(100)- BarCode:- 34533235443234\n0.Back |0|4|EXTRA";
    }

    private function menuLevel5() {
        $this->strDISPLAY = "Select Voucher:\n1.Gift Voucher(500)\n2.Shopping Voucher(1000)|6|EXTRA";
    }

    private function menuLevel6() {
        $this->strDISPLAY = "Enter Amount to Redeem.\n0.Back|7|$this->USSD_STRING";
    }

    private function menuLevel7() {//Get Application Status
        $this->strDISPLAY = "Your Redemption Request is being processed you will receive a confirmation.|0|EXTRA";
    }

    private function menuLevel8() {
        $this->strDISPLAY = "Buy Vouchers:\n1.Send Gift Voucher\n0.Back|9|0";
    }

    private function menuLevel9() {//Check Land Rent
        $this->strDISPLAY = "Enter Receivers Phone Number :|10|$this->USSD_STRING";
    }

    private function menuLevel10() {
        $this->strDISPLAY = " Enter Amount: |11|0";
    }

    private function menuLevel11() {
        $this->strDISPLAY = "Confirm Gift Card of $this->INPUT to ($this->EXTRA)\n 1.Confirm\n 0.Back |12|0";
    }

    private function menuLevel12() {
        $this->strDISPLAY = "Your Request is Being Processed. You will receive a confirmation shortly. |13|0";
    }

    private function menuLevel13() {
        $this->strDISPLAY = "Your Request is being Processed. You will receive a confirmation. |0|0";
    }

    private function menuLevel14() {
        $this->strDISPLAY = "Your Account Balance is Ksh. 3000. You Have 200 Paypoints.|0|0";
    }

    private function menuLevel15() {
        $this->strDISPLAY = "Your statement has been sent to your registered E-mail Address.|0|0";
    }

    private function menuLevel16() {
        $this->strDISPLAY = "Enter Mpesa Paybill Number:|17|0";
    }

    private function menuLevel17() {
        $this->strDISPLAY = "Enter Account Number:|18|$this->USSD_STRING";
    }

    private function menuLevel18() {
        $this->strDISPLAY = "Enter Amount:|19|$this->EXTRA*$this->USSD_STRING";
    }
    
     private function menuLevel19() {
        $Extras = split('*', $this->EXTRA);
        $this->strDISPLAY = "Confirm Payment Amount $this->USSD_STRING to Mpesa Paybill $Extras[0]- Account: $Extras[1]]\n1.Confirm\n0.Back |20|0";
    }
    
    
      private function menuLevel20() {
          
           $this->strDISPLAY = "Your Paybill request is being processed you will receive a confirmation\n0.Back |20|0";
 
      }

}
