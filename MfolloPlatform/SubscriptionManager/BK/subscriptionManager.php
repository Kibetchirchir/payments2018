<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '/apps/MfolloPlatform/Utils/config.php';
require_once '/apps/MfolloPlatform/Utils/MySqlD4M.php';
include_once '/apps/MfolloPlatform/Utils/global-var.php';
class sManager {

    private $MSISDN;
    private $SERVICE;
    private $NETID;
    private $ACTION;
    private $D4M;
    private $FLOGPATH = "/srv/log/Subscription_Info.log";
    
    public function __construct($MSISDN, $SERVICE, $ACTION, $NETID) {
       
        //$this->D4M = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME);
        $this->MSISDN = $MSISDN;
        $this->SERVICE = $SERVICE;
        $this->NETID = $NETID;
        $this->ACTION = $ACTION;
        flog($this->FLOGPATH, "");
        flog($this->FLOGPATH, "");
        flog($this->FLOGPATH, "SUBSCRIBER MSISDN =========== $MSISDN");
        
        $this->logRequest();
        if ($ACTION == 1) {
            $this->subscribe();
        } else {
            $this->unSubscribe();
        }
    }

    private function unSubscribe() {
        $profileID = $this->checkIfProfileExists();
        $serviceID = $this->checkIfServiceExists();
        $subscriptionID = "";
        $status = false;

        if ($serviceID != "") {
            $subscriptionID = $this->checkIfSuscribed($profileID, $serviceID);
            if ($subscriptionID != "") {
                $query = "update subscriptions set status = 2 where profileID = ? and serviceID = ?";
                $params = array('ii', &$profileID, &$serviceID);
                $subscriptionID = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoUpdate($query, $params);
                $status = true;
            } else {
                $status = true;
            }
            
//            $serviceName = $this->getServiceName();
//            
//            $message = "Thank You for using $serviceName. You been successfully unsubscribed.";
//            
//            $this->sendSms($message);
        } else {
            $status = false;
        }
        return $status;
    }

    private function subscribe() {
        $profileID = $this->checkIfProfileExists();
        $serviceID = $this->checkIfServiceExists();
        $subscriptionID = "";
        $status = false;

        if ($serviceID != "") {
            $subscriptionID = $this->checkIfSuscribed($profileID, $serviceID);
            if ($subscriptionID == "") {
                $table = "subscriptions";
                $values = array("serviceID" => $serviceID, "profileID" => $profileID, "status" => "1", "dateCreated" => "now()");
                $subscriptionID = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
                $status = true;
            } else {
                $query = "update subscriptions set status = 1 where profileID = ? and serviceID = ?";
                $params = array('ii', &$profileID, &$serviceID);
                $subscriptionID = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoUpdate($query, $params);
                $status = true;
            }
            $serviceName = $this->getServiceName();
            
            $message = "You have been successfully subscribed for $serviceName. Dial *512# to access the service.";
            
            $this->sendSms($message);
            
        } else {
            $status = false;
        }
        return $status;
    }

    private function checkIfProfileExists() {
        $profileID = "";
        $MSISDN = $this->MSISDN;
        $query = "select profileID from profiles where MSISDN =?";
        $params = array('i', &$MSISDN);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        while ($row = mysqli_fetch_array($result)) {
            $profileID = $row['profileID'];
        }
        if ($profileID == "") {
            $table = "profiles";
            $values = array("MSISDN" => $this->MSISDN, "dateCreated" => "now()", "netID"=> $this->NETID);
            $profileID = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        } else {
            $profileID = $profileID;
        }
        return $profileID;
    }

    private function checkIfServiceExists() {
        $serviceID = "";
        $query = "select serviceID from services where SafaricomID = ? or AirtelID = ? or OrangeID = ?";
        $params = array('sss', &$this->SERVICE, &$this->SERVICE, &$this->SERVICE);
        $result =  D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        while ($row = mysqli_fetch_array($result)) {
            $serviceID = $row['serviceID'];
        }
        return $serviceID;
    }

     private function getServiceName() {
        $serviceName = "";
        $query = "select serviceName from services where SafaricomID = ? or AirtelID = ? or OrangeID = ?";
        $params = array('sss', &$this->SERVICE, &$this->SERVICE, &$this->SERVICE);
        $result =  D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        while ($row = mysqli_fetch_array($result)) {
            $serviceName = $row['serviceName'];
        }
        return $serviceName;
    }
    private function checkIfSuscribed($profileID, $serviceID) {
        $pSubID = "";
        $query = " select subscriptionID from subscriptions where profileID =? and serviceID = ?";
        $params = array('ii', &$profileID, &$serviceID);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        while ($row = mysqli_fetch_array($result)) {
            $pSubID = $row['subscriptionID'];
        }
        return $pSubID;
    }

    private function logRequest()
    {
         $table = "subscriptionRequestLogs";
         $values = array("MSISDN" => $this->MSISDN, "SERVICE" => $this->SERVICE, "ACTION"=>  $this->ACTION, "NETID"=> $this->NETID);
         $SID = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
         
         return $SID;
    }
    
    private function sendSms($message)
    {
        $sc = '21505';
        if($this->SERVICE == '6015182000098323')
        {
            $sc='21505';
            
        }  else if($this->SERVICE == '6015182000101320') {
            $sc='21504';
        } else if ($this->SERVICE=='6015182000103771')
        {
            $sc='22430';
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1/MfolloPlatform/APIs/smsproxy.php?MSISDN=".$this->MSISDN."&MESSAGE=".rawurlencode($message)."&SOURCE=$sc");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch) . curl_error($ch);
        
        flog($this->FLOGPATH, "SEND SMS | =========== /n $output");
        curl_close($ch);
    }
}
