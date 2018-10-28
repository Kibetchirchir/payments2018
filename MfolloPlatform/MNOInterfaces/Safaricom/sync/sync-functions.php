<?php
include_once '../../../Utils/config.php';
include_once '../../../Utils/global-var.php';
include_once '../../../Utils/MySqlD4M.php';
include_once '../../../SubscriptionManager/subscriptionManager.php';
class FunctionsClass {

    
    public $FLOGPATH = "/srv/log/Subscription_Info.log";

    function syncOrderRelation($request) {
        
        flog($this->FLOGPATH, print_r($request, true));
        $postdata = file_get_contents("php://input");
        flog($this->FLOGPATH, "" . $postdata);  
        $MSISDN = $request->userID->ID;
      //$SERVICE = $request->productID;
        $SERVICE = $request->serviceID;
        $ACTION = $request->updateType;
        $NETID = 1;
        
        new sManager($MSISDN, $SERVICE, $ACTION, $NETID);
        //prepare response
        $response = new stdClass();
        $response->result = 0;
        $response->resultDescription = "OK";
        return $response; 
    }

    function syncMSISDNChange() {
        
    }

    function syncSubscriptionData($request) {
        
    }

    function changeMSISDN($request) {
        
    }

}

?>
