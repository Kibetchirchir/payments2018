<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '/apps/MfolloPlatform/Utils/config.php';
require_once '/apps/MfolloPlatform/Utils/MySqlD4M.php';
include_once '/apps/MfolloPlatform/Utils/global-var.php';

class Navigator {

    private $MSISDN;
    private $SERVICE_CODE;
    private $USSD_STRING;
    private $SESSION_ID;
    private $OPCODE;
    private $D4M;
    private $SERVICES;

    public function __construct($MSISDN, $SERVICE_CODE, $USSD_STRING, $SESSION_ID = "") {

        $this->MSISDN = $MSISDN;
        $this->SERVICE_CODE = $SERVICE_CODE;
        $this->USSD_STRING = $USSD_STRING;
        $this->SESSION_ID = $SESSION_ID;
       // $this->D4M = D4M::createInstance("localhost", "root", "2014tasha", "mfollo");
        $this->logRequest();
    }

    private function logRequest() {//Function Logs all Hops
        $table = "ussdRequestLogs";
        $values = array("MSISDN" => $this->MSISDN, "SERVICE_CODE" => $this->SERVICE_CODE, "SESSION_ID" => $this->SESSION_ID, "USSD_STRING" => $this->USSD_STRING, "dateCreated" => "now()");
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        return $logged;
    }

    private function getProfileID() {
        $profileID = 0;
        $query = "select profileID from profiles where MSISDN =?";
        $params = array('i', &$this->MSISDN);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        while ($row = mysqli_fetch_array($result)) {
            $profileID = $row["profileID"];
        }
        return $profileID;
    }

    public function processRequest() {
        $menu = "";
      //  echo "started process request";
            $services = $this->getServices();
            $_SESSION['SERVICES'] = $services;
            $this->SERVICES = $services;
            $serviceCount = sizeof($services);
            
        if (!$this->checkIfSessionExists()) {
            $_SESSION['MSISDN'] = $this->MSISDN;

            if ($serviceCount > 1) {
                $menu = $this->fomulateServiceMenu();
                $_SESSION['OPCODE'] = "BEG";
            } else if ($services[0]["serviceID"] != "" && $serviceCount == 1) {
              //  echo 'Only one service found';
              //  print_r ($services);
                $URL = $services[0]['URL'];
                require_once "../Menus/$URL";
                $menu = Menu::createInstance($this->MSISDN, $this->USSD_STRING)->processMenu();
                $_SESSION['OPCODE'] = "CON";
                  $_SESSION["MENU_INDEX"] = 0;
            } else {
               $menu = "You have not subscribed for any service on this mobile platform kindly send in your service's 'keyword' to 21505";
            }
        } else {
                if($_SESSION['OPCODE'] == "BEG")
                {
                   $index = $this->USSD_STRING -1;
                   $URL = $services[$index]['URL'];
                   $_SESSION["MENU_INDEX"] = $index;
                   
                }else                    
                {
                    $index = $_SESSION["MENU_INDEX"];
                    $URL = $services[$index]['URL'];
                }
                
                require_once '../Menus/'.$URL;
                $menu = Menu::createInstance($this->MSISDN, $this->USSD_STRING)->processMenu();
                $_SESSION['OPCODE'] = "CON";
   
        }

        return $menu;
    }

    private function checkIfSessionExists() {
        return isset($_SESSION["MSISDN"]);
    }

//    private function createSession() {
//        session_id($this->MSISDN);
//        session_start();
//      //  echo '\n New Session Created/Resumed';
//    }

    private function fomulateServiceMenu() {
        $menu = "Select Service\n";
        $services = $this->SERVICES;
        for ($x = 0,$i=1; $x <= (sizeof($services) - 1); $x++, $i++) {
            $menu.= $i .".".$services[$x]['displayName']."\n";
        }
        return $menu;
    }

    private function getServices() {
        $x = 0;
        $profileID = $this->getProfileID();
        $services = array(array('serviceID'=>'', 'URL'=>'', 'displayName'=>'', 'keyword'=>''));
        $query = "select s.serviceID, s.URL, s.displayName, s.keyword  from services s join subscriptions sb on s.serviceID = sb.serviceID where sb.profileID = ?";
        $params = array('i', &$profileID);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        while ($row = mysqli_fetch_array($result)) {
            $services[$x]["serviceID"] = $row[0];
            $services[$x]["URL"] = $row[1];
            $services[$x]["displayName"] = $row[2];
            $services[$x]["keyword"] = $row[3];
            $x++;
        }
  
        return $services;
    }

}
