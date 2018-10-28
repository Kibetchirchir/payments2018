<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once '/apps/WalletGateway/Utils/config.php';
include_once '/apps/WalletGateway/Utils/MySqlD4M.php';

class AgentDeposit {

    protected $apiClientID;
    protected $transactionID;
    protected $agentNumber;
    protected $amountPaid;
    protected $MSISDN;
    protected $IDNumber;
    protected $Token;
    protected $api_key = '';
    protected $api_secret = '';

    public function __construct() {
        if (isset($_POST['apiClientID']))
            $apiClientID = $_POST['apiClientID'];
        if (isset($_POST['transactionID']))
            $transactionID = $_POST['transactionID'];
        if (isset($_POST['agentNumber']))
            $agentNumber = $_POST['agentNumber'];
        if (isset($_POST['MSISDN']))
            $MSISDN = $_POST['MSISDN'];
        if (isset($_POST['IDNumber']))
            $IDNumber = $_POST['IDNumber'];
        if (isset($_POST['amountPaid']))
            $amountPaid = $_POST['amountPaid'];
        if (isset($_POST['Token']))
            $Token = $_POST['Token'];


        $this->processDeposit();
    }

    private function validateToken() {
        $isValid = false;
        $Message = "2" + $this->api_key + $this->transactionID + $this->amountPaid;
        $s = hash_hmac('sha256', $Message, $this->api_secret, true);
        $ourToken = base64_encode($s);

        if ($this->Token == $ourToken) {
            $isValid = true;
        }
        return $isValid;
    }

    private function processDeposit() {
        if ($this->validateToken()) {
            $GUID = $this->insertPayments();
            if (is_numeric($GUID) && $GUID > 0) {
                return $this->formulateResponse('200', 'Successful Transactions', $this->transactionID, $GUID);
            } else {
                return $this->formulateResponse('666', 'Unable to Process. Ensure this is not a duplicate transaction', $this->transactionID, '0');
            }
        } else {
            return $this->formulateResponse('666', 'Unauthorized User', $this->transactionID, '0');
        }
    }

    private function formulateResponse($statusCode, $statusDesc, $trxID, $GUID) {
        return json_encode(array($statusCode, $statusDesc, $trxID, $GUID));
    }

    private function insertPayments() {
        $paymentGatewayID =10;
        $table = "paymentLogs";
        $values = array("trxID" => $this->transactionID, "paymentGatewayID" => $paymentGatewayID, "amount" => $this->amountPaid, "MSISDN" => $MSISDN, "trxAccountNo" => $this->agentNumber, "trxSenderName" => $this->IDNumber, "status" => '0', "dateCreated" => date("Y:m:d HH:MM:SS"));
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        return $logged;
    }

}
