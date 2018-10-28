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
            $menu = "Sorry GHRIS Service is currently unavailable. Kindly Bare with us as we work on fixing this issue";
        }


        return $menu;
    }

    private function menuLevel0() {

        $this->strDISPLAY = "Welcome to LANDS Mobile Service\n1.Register Your Land\n2.Track Application\n3.Check Land Rent. |1|EXTRA";
    }

    private function menuLevel1() {
        if ($this->USSD_STRING == "1") {
            $this->menuLevel2();
        } else if ($this->USSD_STRING == "2") {
            $this->menuLevel7();
        } else if ($this->USSD_STRING == "3") {
            $this->menuLevel9();
        } else {
            $this->menuLevel0();
        }
    }

    private function menuLevel2() {
        $this->strDISPLAY = "Enter You Title Number:|3|EXTRA";
    }

    private function menuLevel3() {
        $this->strDISPLAY = "Enter Your Full Names:|4|EXTRA";
    }

    private function menuLevel4() {
        $this->strDISPLAY = "Enter your ID Number:|5|EXTRA";
    }

    private function menuLevel5() {
        $this->strDISPLAY = "Enter your Land Rent Amount:(Zero if Free-Hold):|6|EXTRA";
    }

    private function menuLevel6() {
        $this->strDISPLAY = "Your Details Have Been Successfully Saved. You will receive a notification once your Details have been Confirmed:|0|EXTRA";
    }

    private function menuLevel7() {//Get Application Status
        $this->strDISPLAY = "Enter Application No.|8|EXTRA";
    }

    private function menuLevel8() {
        $this->strDISPLAY = "Your request is being processed. You will receive a reply shortly with your application details. Thank you for using LANDS Mobile Service.\n0.Back|0|0";
    }

    private function menuLevel9() {//Check Land Rent
        $this->strDISPLAY = "Enter your Title Number:|10|0";
    }

    private function menuLevel10() {
        $this->strDISPLAY = "Your request is being processed. You will receive a reply shortly with your application details. Thank you for using LANDS Mobile Service.|0|0" + strINPUT;
    }

}
