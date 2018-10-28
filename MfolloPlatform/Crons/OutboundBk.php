<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class INMESSAGES {

    private $MSISDN = NULL;
    private $MESSAGEID = NULL;
    private $MESSAGE = NULL;
    private $HOST = "localhost";
    private $DBUSER = "root";
    private $DBPWD = "2014tasha";
    private $DBNAME = "mfollo";
    private $DEST = "./";

    public function processSMS() {
        while (true) {
            require_once $this->DEST . "MySqlD4M.php";

            $result = D4M::createInstance("$this->HOST", "$this->DBUSER", "$this->DBPWD", "$this->DBNAME")->DoSelect('select outboundID, MSISDN, message from outbound where status = 0 order by outboundID desc limit 50', NULL);
            while ($row = mysqli_fetch_array($result)) {
                $this->MSISDN = $row["MSISDN"];
                $this->MESSAGEID = $row["outboundID"];
                $this->MESSAGE = $row["message"];

                $url = "http://localhost/MfolloPlatform/MNOInterfaces/Safaricom/sendsms/index.php?SPID=601518&SERVICEID=6015182000098323&MESSAGE=" . rawurlencode($this->MESSAGE) . "&SOURCE=21505&DEST=" . $this->MSISDN . "&LINKID=0&SMSID=" . $this->MESSAGEID;
                //$cresult = join(" ", $url );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $cresult = curl_exec($ch) . curl_error($ch);

                preg_match_all("#<ns1:result>([^<]+)</ns1:result>#", $cresult, $foo);
                $gatewayUniqueID = "0";
                $gatewayUniqueID = rawurlencode(implode("\n", $foo[1]));

                
                echo $this->MESSAGEID . "Result=>   \n" . $cresult;
                
                echo "Gateway Unique ID => ".$gatewayUniqueID;

                $wresult = D4M::createInstance("$this->HOST", "$this->DBUSER", "$this->DBPWD", "$this->DBNAME")->DoUpdate('update outbound set status=1, gatewayUniqueID=? where outboundID=?', array('si', &$gatewayUniqueID, &$this->MESSAGEID));
            }
        sleep(5);
        echo "Trying to get records to process ....................... \n";
            
        }
        
        
    }

}

$SMS = new INMESSAGES();
$SMS->processSMS();
?>
