<?php
include_once '/var/www/html/pesadirect/MfolloPlatform/Utils/config.php';
require_once '/var/www/html/pesadirect/MfolloPlatform/Utils/MySqlD4M.php';
include_once '/var/www/html/pesadirect/MfolloPlatform/Utils/global-var.php';
include_once '/var/www/html/pesadirect/MfolloPlatform/SubscriptionManager/subscriptionManager.php';
class FunctionsClass 
{
    private $FLOGPATH = "/srv/log/IncomingMessages_Info.log";
    function FunctionsClass() { }

    function notifySmsReception($request) {
        try {
            flog($this->FLOGPATH, "notifySmsReception()");
            flog($this->FLOGPATH, print_r($request, true));

            $correlator = $request->correlator;
            $message = $request->message->message;
            $MSISDN = str_replace("tel:", "", $request->message->senderAddress);
            $SHORTCODE = str_replace("tel:", "", $request->message->smsServiceActivationNumber);
            $msg_date = $request->message->dateTime;

            if (substr($MSISDN, 0, 3) != '254') {
                $MSISDN = "254" . $MSISDN;
            }

$postdata = file_get_contents("php://input");
flog($this->FLOGPATH, print_r($postdata, true));
            preg_match_all("#<ns1:linkid>([^<]+)</ns1:linkid>#", $postdata, $foo);
            $linkId = "";
            $linkId = rawurlencode(implode("\n", $foo[0]));
            preg_match_all("#<ns1:traceUniqueID>([^<]+)</ns1:traceUniqueID>#", $postdata, $foo);
            $SDPID = "";
            $SDPID = rawurlencode(implode("\n", $foo[1]));
            
              if ($SHORTCODE==997)
{
$messageMchoice ="Thank you for participating in TEST SERVICE program";
$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/MfolloPlatform/APIs/smsproxy.php?LINKID=$linkId&MSISDN=".$this->MSISDN."&MESSAGE=".rawurlencode($message)."&SOURCE=$SHORTCODE");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch) . curl_error($ch);
        
        flog($this->FLOGPATH, "SEND SMS | =========== /n $output");
        curl_close($ch);
}


            
            
             $table = "inbound";
             $values = array("MSISDN" => $MSISDN, "message" => $message, "gatewayUniqueID" => $SDPID, "linkID" => $linkId, "shortcode" => $SHORTCODE, "dateCreated" => $msg_date);
             $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
              return $logged;
//MO Processing On demand messages
  
           
 if(strtolower($message) == "password")
             {
                 $SERVICE = "MDSP2000204268";
                 $ACTION =  1;
                 $NETID = 1;
                 new sManager($MSISDN, $SERVICE, $ACTION, $NETID);
             } 
             
             if(strtolower($message) == "pass")
             {
                 $SERVICE = "MDSP2000204270";
                 $ACTION =  1;
                 $NETID = 1;
                 new sManager($MSISDN, $SERVICE, $ACTION, $NETID);
             }  
             
       
            
        } catch (Exception $ex) {

             flog($this->FLOGPATH,"ERROR: " . $ex->getMessage() . $ex->getTrace());
        }
    }

    function notifySmsDeliveryReceipt($request) {
      
         flog($this->FLOGPATH, "notifySmsDeliveryReceipt()");
         flog($this->FLOGPATH, print_r($request, true));
         $postdata = file_get_contents("php://input");
         flog($this->FLOGPATH, "" . $postdata);

        $correlator = $request->correlator;
        $str_report = $request->deliveryStatus->deliveryStatus;
      //  $reportcode = $delivery[$str_report];
        $destaddr = str_replace("tel:", "", $request->deliveryStatus->address);

        $correl_data = explode("x", $correlator);
        $correl = $correl_data[0];
        $sourceaddr = "705";

        if (substr($destaddr, 0, 3) != '254') {
            $destaddr = "254" . $destaddr;
        }
      
         $table = "deliveryReports";
         $values = array("MSISDN" => $destaddr, "outboundID" => $correl, "deliveryStatus" => $str_report, "shortcode" => '994', "dateCreated" => "now()");
         $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
             
         if ($str_report == "DeliveryImpossible" || $str_report == "Insufficient_Balance")
         {
               $query = "Update outbound set status =0, bucketID=0 where outboundID=?";
               $params = array('s', &$correl );
               $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoUpdate($query, $params);
         }
         
       
    }

}

?>