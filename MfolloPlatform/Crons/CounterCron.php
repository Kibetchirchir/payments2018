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
    private $DBNAME = "mfollo";
    private $DEST = "./";

    public function processSMS() {
       while (true){ 
        require_once $this->DEST . "MySqlD4M.php";
          
            $result = D4M::createInstance("$this->HOST", "$this->DBUSER", "$this->DBPWD", "$this->DBNAME")->DoSelect('select count(*) Counter from subscriptions where status =1', NULL);
            while ($row = mysqli_fetch_array($result)) {
             
             echo $row["Counter"]. "\n";
                  
            }
            
sleep(5);
       }
     
    }

}

$SMS = new INMESSAGES();  
$SMS->processSMS();
?>
