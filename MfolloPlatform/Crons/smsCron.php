<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



class INMESSAGES {

    private $MSISDN = NULL;
    private $MESSAGEID = NULL;
    private $LINKID = NULL;
    private $HOST = "localhost";
    private $DBUSER = "root";
    private $DBPWD = "K!l0T@tu";
    private $DBNAME = "smsChannel";
    private $DEST = "./";

    public function processSMS() {
    //   while (true){ 
        require_once $this->DEST . "MySqlD4M.php";
          
            $result = D4M::createInstance("$this->HOST", "$this->DBUSER", "$this->DBPWD", "$this->DBNAME")->DoSelect('select inboundID, MSISDN, linkID  from inbound where status = 0', NULL);
            while ($row = mysqli_fetch_array($result)) {
             $this->MSISDN = $row["MSISDN"];
             $this->MESSAGEID = $row["inboundID"];
             $this->LINKID = $row["linkID"];
             
             $message = "Thank You for subscribing for Bridging Mobile service!";
             
             $url = "http://41.220.125.194/SafaricomSdpChannel/sendsms/index.php?SPID=601551&SERVICEID=6015512000097560&MESSAGE=".  rawurlencode($message)."&SOURCE=20365&DEST=". $this->MSISDN."&LINKID=".$this->LINKID."&SMSID=". $this->MESSAGEID;
             $cresult = join(" ", $url );
             echo $this->MESSAGEID."=>".$cresult;
             $wresult = D4M::createInstance("$this->HOST", "$this->DBUSER", "$this->DBPWD", "$this->DBNAME")->DoUpdate('update inbound set status=1 or status = NULL where inboundID=?', array('i', &$this->MESSAGEID));
      
            }
            
            echo "\n Tried";
       //}
        return $wresult;
    }

}

$SMS = new INMESSAGES();  
$SMS->processSMS();
?>
