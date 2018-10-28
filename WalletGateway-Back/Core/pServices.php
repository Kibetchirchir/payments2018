<?php

include_once '/apps/WalletGateway/Utils/config.php';
include_once '/apps/WalletGateway/Utils/MySqlD4M.php';
/* Evid Sibi on a drowsy day wrote this. */

class pServices {

    private $ACC_NO;
    private $ACC_BAL;

    protected function Paybill($IDNumber, $MSISDN, $AccType, $BillRef, $Amount) {
        return "OK";
    }

    protected function BuyAirtime($MSISDN, $Amount) {
        return "OK";
    }

    protected function CashOut($MSISDN, $Amount) {
        return "OK";
    }

    protected function CashIn($IDNumber, $MSISDN, $AccType, $BillRef, $Amount) {
        return "OK";
    }

    protected function FundsTransfer($MSISDN, $Amount) {
        return "OK";
    }

    protected function BalanceInquiry($MSISDN, $Amount) {
        return "OK";
    }

    protected function eServices($IDNumber, $MSISDN, $AccType, $BillRef, $Amount, $clientMSISDN, $clientIDNumber, $clientName, $serviceID) {
        // echo $BillRef;
        if ($this->getAccountDetails($IDNumber)) {
            if($Amount <= $this->ACC_BAL)
            {
            $this->updateAccountBalance($this->ACC_NO, $Amount, 0, false);
            $trxID = $this->insertTransactions($this->ACC_NO, $serviceID, $Amount, '0', $clientMSISDN, $clientIDNumber, $clientName, $BillRef);
            $this->insertPayments($trxID, 9, $MSISDN, $Amount, $BillRef, $this->ACC_NO);
            $this->updateBillStatus($BillRef, $MSISDN, $IDNumber, $Amount, 9);
            $this->Notify($trxID, $Amount, $BillRef, $IDNumber);

            return "OK";
            }  else {
                
            return "Sorry you have insufficient balance please. Please top up your account in order complete this payment!!";
                
            }
        } else {
            return "Your account is not A Valid Agent Account!!!";
        }
    }

    private function getAccountDetails($IDNumber) {

        $isValid = false;
        $query = "select accountID, availableBalance from accounts where IDNumber=?";
        $params = array('s', &$IDNumber);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoSelect($query, $params);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $this->ACC_NO = $row['accountID'];
                $this->ACC_BAL = $row['availableBalance'];
            }

            $isValid = true;
        } else {
            $isValid = false;
        }

        return $isValid;
    }

    private function updateAccountBalance($accountID, $Amount, $Commission, $isCredit) {

        if ($isCredit) {
            $query = " update accounts set credit=credit+$Amount, availableBalance=availableBalance+$Amount where accountID= ? ";
        } else {
            $query = "update accounts set debit=debit+$Amount, availableBalance=availableBalance-$Amount, commission=commission+$Commission where accountID = ? ";
        }
        $params = array('i', &$accountID);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoUpdate($query, $params);

        return $result;
    }

    private function insertTransactions($accountID, $serviceID, $Amount, $Commission, $clientMSISDN, $clientIDNumber, $clientName, $billRefNo) {
        $table = "transactions";
        $values = array("accountID" => $accountID, "serviceID" => $serviceID, "amount" => $Amount, "commission" => $Commission, "MSISDN" => $clientMSISDN, "IDNumber" => $clientIDNumber, "name" => $clientName, "status" => '1', 'billRefNo'=>$billRefNo, "dateCreated" => date("Y:m:d HH:MM:SS"));
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        return $logged;
    }

    private function insertPayments($trxID, $paymentGatewayID, $MSISDN, $amount, $trxAccountNumber, $trxSenderName) {
        $table = "paymentLogs";
        $values = array("trxID" => $trxID, "paymentGatewayID" => $paymentGatewayID, "amount" => $amount, "MSISDN" => $MSISDN, "trxAccountNo" => $trxAccountNumber, "trxSenderName" => $trxSenderName, "status" => '1', "dateCreated" => date("Y:m:d HH:MM:SS"));
        $logged = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoInsert($table, $values);
        return $logged;
    }

    private function updateBillStatus($billRefNo, $MSISDN, $IDNumber, $amount, $pgID) {
        $payerDetails = json_encode(array("IDNumber" => $IDNumber));
        $query = "update bills set payerMSISDN=?, payerDetails=?, amountPayed=?, status=1, paymentGatewayID=? where billRefNumber = ? ";
        $params = array('sssss', &$MSISDN, &$payerDetails, &$amount, &$pgID, &$billRefNo);
        $result = D4M::createInstance(Props::$DBHOST, Props::$DBUSER, Props::$DBPASS, Props::$DBNAME)->DoUpdate($query, $params);

        return $result;
    }

    private function Notify($trxID, $trxAmount, $BillRef, $IDNumber) {

        $api_key = "9283475jkhtw9844";
        $invoice_number = $BillRef;
        $transaction_id = $trxID;
        $transaction_date = date('Y-m-d H:i:s');
        $transaction_status = "paid"; //or failed
        $amount = $trxAmount;
        $paidby = $IDNumber;

//set POST variables
        $fields = array(
            'apikey' => urlencode($api_key),
            'apisecret' => urlencode($api_key),
            'invoice' => urlencode($invoice_number),
            'transactionid' => urlencode($transaction_id),
            'transactiondate' => urlencode($transaction_date),
            'transactionstatus' => urlencode($transaction_status),
            'amount' => urlencode($amount),
            'paidby' => urlencode($paidby),
        );

        $url = "http://ntsa.campfossa.org/index.php/payment/updateinvoice";

//url-ify the data for the POST
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        //  echo $fields_string;
        rtrim($fields_string, '&');

//open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        // curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       
//execute post
        $result = curl_exec($ch);

//close connection
        curl_close($ch);

//The result return is json format so you can convert it into an array
        $query_details = json_decode($result);
    }

}
