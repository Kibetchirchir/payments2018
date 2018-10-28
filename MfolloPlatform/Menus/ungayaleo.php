<?php
//require_once "/var/www/html/cons/sets.php";
$MSISDN = $_GET['MSISDN'];
$INPUT = $_GET['USSD_STRING'];
$LEVEL = $_GET['NEXT_LEVEL'];
$EXTRA = $_GET['EXTRA'];
$message = "";
$DESTADDR = 7955;
$MfolloNumb = 254721912151;
$INPUT = strip_tags($INPUT);  // not neccssary for none HTML
$INPUT= trim(preg_replace("/\s+/"," ",$INPUT));
$DB_connect = mysql_connect ("localhost", "root", "2014tasha") or die("cannot connect to localhost" . mysql_error($DB_connect));
mysql_select_db('ungayaleo', $DB_connect)  or die("cannot connect " . mysql_error($DB_connect));
$keywordSearch ='ungayaleo';

if($LEVEL=='0')
 {
$message="4^Welcome to Unga Ya Leo\n1.Request for Unga ya Leo\n2.Buy Goods\n3.Check Balance\n4.Tell a friend\n5.About Unga ya Leo\n6.Accept Token^CON^EXTRA";

}

if($LEVEL=='4')
{
if ($INPUT=='1' )
{
//check if the user has any outstanding balance
$statusCheck = "select MSISDN from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
			$noAccs = mysql_num_rows($result);
                         if($noAccs>0) //have an account
{
    $statusCheck = "select MSISDN from accounts where MSISDN = $MSISDN and status =300";
$result = selectSQL($statusCheck, $DB_connect);
			$noAccs = mysql_num_rows($result);
                         if($noAccs>0) //have an account
{
 $message= "0^Sorry, you have an outstanding balance.Please repay to continue enjoying Unga ya Leo service^END^EXTRA"; 
}
else 
{
 $message= "10^Choose amount of token\n1.50\n2.100\n3.150\n4.200\n5.250^CON^EXTRA";   
}
}
else 
{
$insertCache = "insert into  accounts values ('',$MSISDN,0,0,200,now(),'')";
$result = selectSQL($insertCache, $DB_connect);
if($result =='TRUE') 
{
$message= "10^Choose amount of token\n1.50\n2.100\n3.150\n4.200\n5.250^CON^EXTRA";   
}
else 
{
$message= "0^Sorry,something went wrong. Please try again later^END^EXTRA";   
}
}
}
else if ($INPUT ==2)
{
 $message= "20^1.Super Markets\n2.Fast Food\n3.Bus Fare^CON^EXTRA";   

}
else if ($INPUT==3)
{
$statusCheck = "select credits from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
			$noAccs = mysql_num_rows($result);
                         if($noAccs>0) //have an account
{
while($row = mysql_fetch_assoc($result))
			 { 
                               $credits = $row["credits"];
                         } 


$message= "0^Your Current Unga ya Leo credit is KSh $credits ^END^EXTRA";   
}
else 
{
$message= "0^Sorry,You have no credit since you have not requested for any token^END^EXTRA";   
}
}
else if ($INPUT==4) {$message="5^Enter phone number of a friend you want to tell about Unga ya Leo^CON^EXTRA";}
else if ($INPUT==5) {$message="1^Unga ya Leo enables individuals and families put a meal on the table when they are not able to find a meal^CON^EXTRA";}
else if ($INPUT==6) {$message="45^Please Enter token number^CON^EXTRA";}
else
{
$message="4^Wrong Input\nWelcome to Unga Ya Leo\n1.Request for Unga ya Leo\n2.Buy Goods\n3.Check Balance\n4.Tell a friend\n5.About Unga ya Leo\n6.Accept Token^CON^EXTRA";
}
}

if($LEVEL==45)
{
$token =$INPUT;
$statusCheck = "select token,assignedMSISDN from tokens where used = 0 and token = $token and assignedMerchant =$MSISDN and time_to_sec(timediff(now(),dateCreated)) < 600 ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
if($noAccs>0) //have an account
{
while($row = mysql_fetch_assoc($result))
			 { 
                               $assignedMSISDN = $row["assignedMSISDN"];
                         } 
$message="46^Please Enter Amount^CON^$assignedMSISDN*$token";

}
else
{
$message= "0^Sorry,The token entered is invalid^END^EXTRA";   
}
}

if($LEVEL==46)
{
$mydata=explode("*",$EXTRA);
$amount=$INPUT;
$assignedMSISDN=$mydata[0];
$token=$mydata[1];
$statusCheck = "select credits from accounts where MSISDN = $assignedMSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
while($row = mysql_fetch_assoc($result))
			 { 
                               $currentCredits = $row["credits"];
                         } 


if ($amount > $currentCredits)
{
$message= "0^Sorry,$amount specified is more than the customers available credit^END^EXTRA";   
}
else
{
$updateToken = "update tokens set used =1 where token = $token and assignedMSISDN = $assignedMSISDN";
$result = selectSQL($updateToken, $DB_connect);

$credit = $currentCredits - $amount;
$updateCredit = "update accounts set credits = $credit where MSISDN = '$assignedMSISDN' ";
$result = selectSQL($updateCredit, $DB_connect);


$MESSAGE1 ="Merchant $MSISDN has deducted $amount from your Unga ya Leo Account";
sendMessage($MESSAGE1,$assignedMSISDN); 

$marchMes = "The $amount from $assignedMSISDN account has been credited into your Unga ya Leo Merchants Account";
sendMessage($marchMes,$MSISDN); 

$message= "0^The $amount from $assignedMSISDN account has been credited into your Unga ya Leo Merchants Account^END^EXTRA";  

}
}

if($LEVEL==10)
{

$statusCheck = "select credits from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
while($row = mysql_fetch_assoc($result))
			 { 
                               $currentCredits = $row["credits"];
                         } 
if ($INPUT==1)
{
$credit1=50;
$credit = $credi1t+$currentCredits;
$updateCredit = "update accounts set credits = $credit, status = 300 where MSISDN = '$MSISDN' ";
$updtime = selectSQL($updateCredit, $DB_connect);
$insertCredit = "insert into credits values ('',0,$MSISDN,'$credit1',now())";
$updtime = selectSQL($insertCredit, $DB_connect);
$mes="Your $MSISDN Unga ya  Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ";
sendMessage($mes,$MSISDN); 
$message= "0^Your Unga ya Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ^CON^EXTRA";   

}
else if ($INPUT==2)
{
$credit1=100;
$credit = $credit1+$currentCredits;
$updateCredit = "update accounts set credits = $credit, status = 300 where MSISDN = '$MSISDN' ";
$updtime = selectSQL($updateCredit, $DB_connect);
$insertCredit = "insert into credits values ('',0,$MSISDN,'$credit1',now())";
$updtime = selectSQL($insertCredit, $DB_connect);
$mes="Your $MSISDN Unga ya  Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ";
sendMessage($mes,$MSISDN);  

$message= "0^Your Unga ya Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ^CON^EXTRA";  

}
else if ($INPUT==3)
{
$credit1=150;
$credit = $credit1+$currentCredits;
$updateCredit = "update accounts set credits = $credit, status = 300 where MSISDN = '$MSISDN' ";
$updtime = selectSQL($updateCredit, $DB_connect);
$insertCredit = "insert into credits values ('',0,$MSISDN,'$credit1',now())";
$updtime = selectSQL($insertCredit, $DB_connect);
$mes="Your $MSISDN Unga ya  Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ^CON^EXTRA";  


}
else if ($INPUT==4)
{
$credit1=200;
$credit = $credit1+$currentCredits;
$updateCredit = "update accounts set credits = $credit, status = 300 where MSISDN = '$MSISDN' ";
$updtime = selectSQL($updateCredit, $DB_connect);
$insertCredit = "insert into credits values ('',0,$MSISDN,'$credit1',now())";
$updtime = selectSQL($insertCredit, $DB_connect);
$mes="Your $MSISDN Unga ya  Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ";
sendMessage($mes,$MSISDN);   
$message= "0^Your Unga ya Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ^CON^EXTRA"; 


}
else if ($INPUT==5)
{
$credit1=250;
$credit = $credit1+$currentCredits;
$updateCredit = "update accounts set credits = $credit, status = 300 where MSISDN = '$MSISDN' ";
$updtime = selectSQL($updateCredit, $DB_connect);
$insertCredit = "insert into credits values ('',0,$MSISDN,'$credit1',now())";
$updtime = selectSQL($insertCredit, $DB_connect);
$mes="Your $MSISDN Unga ya  Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo account has been toped up with $credit1 KSh worth of Credit.Your new balance is $credit ^CON^EXTRA";  

}
else 
{
 $message= "10^Wrong Input\nChoose amount\n1.50\n2.100\n3.150\n4.200\n5.250^CON^EXTRA";   

}

}


if ($LEVEL==20)
{

if($INPUT==1)
{
$message= "21^1.Local Duka\n2.Uchumi\n3.Nakumatt\n4.Tuskys\n5.Naivas\n6.Ukwala^CON^EXTRA";   
}
else if ($INPUT==2)
{
$message= "31^1.Galitos\n2.Kenchic\n3.Steers\n4.Others^CON^EXTRA";   
}
else if ($INPUT==3)
{
$message= "41^1.Double M\n2.Citi Hoppa\n3.KBS\n4.Matatu^CON^EXTRA";   
}
else 
{
$message= "20^Wrong Input\nSelect\n1.Super Markets\n2.Fast Food\n3.Bus Fare^CON^EXTRA";   
}
}

if($LEVEL==21)
{

$statusCheck = "select credits from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
while($row = mysql_fetch_assoc($result))
			 { 
                               $currentCredits = $row["credits"];
                         } 
if($INPUT==1)
{
$message= "22^Enter the Shopkeeper's phone number^CON^EXTRA";   
}
else if ($INPUT==2)
{
$merchant ="Uchumi";

$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN); 
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 
  

}
else if ($INPUT==3)
{
$merchant ="Nakumatt";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA";  
 
}
else if ($INPUT==4)
{
$merchant ="Tuskys";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 
  

}
else if ($INPUT==5)
{
$merchant ="Naivas";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);   
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 
 

}
else if ($INPUT==6)
{
$merchant ="Ukwala";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA";
   

}
else 
{
$message= "21^Wrong Input\n1.Local Duka\n2.Uchumi\n3.Nakumatt\n4.Tuskys\n5.Naivas\n6.Ukwala^CON^EXTRA";   
}

}


if($LEVEL==22)
{
$phone=$INPUT; 
$checkstr = strlen($INPUT) ;
if(( !is_numeric($INPUT)) and (( $checkstr < 9) or ($checkstr > 12 ) ))
{
$message= "22^Enter a valid Shopkeeper's phone number^CON^EXTRA";   
}
else
{
$checkNum = substr($phone, 0,4 );
if ($checkNum == 2547)
{
$phone = $phone;
} 
else
{
$phone = substr($phone,1);
$phone = 254 . $phone;
}

$statusCheck = "select credits from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
while($row = mysql_fetch_assoc($result))
			 { 
                               $currentCredits = $row["credits"];
                         } 
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,$phone,now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used by $phone is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);   
$message= "0^Your Unga ya Leo token to be used by $phone is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 
 
}
}


if ($LEVEL==31)
{

$statusCheck = "select credits from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
while($row = mysql_fetch_assoc($result))
			 { 
                               $currentCredits = $row["credits"];
                         }  

if($INPUT==1)
{
$merchant ="Galitos";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA";
 
}
else if ($INPUT==2)
{
$merchant ="Kenchic";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 

}
else if ($INPUT==3)
{
$merchant ="Steers";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN); 
$message= "0^Your Unga ya Leo token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA";  

}
else if ($INPUT==4)
{
$message= "32^Please Enter the Merchants phone number^CON^$currentCredits";   
}
else
{
$message= "31^Wrong Input\nSelect\n1.Galitos\n2.Kenchic\n3.Steers\n4.Others^CON^EXTRA";   
}
}


if ($LEVEL==32)
{
$phone=$INPUT; 
$currentCredits=$EXTRA;
$checkstr = strlen($INPUT) ;
if(( !is_numeric($INPUT)) and (( $checkstr < 9) or ($checkstr > 12 ) ))
{
$message= "32^Please Enter a valid Merchants phone number^CON^$currentCredits";   
}
else
{
$checkNum = substr($phone, 0,4 );
if ($checkNum == 2547)
{
$phone = $phone;
} 
else
{
$phone = substr($phone,1);
$phone = 254 . $phone;
}

$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,$phone,now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo token to be used by $phone is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo token to be used by $phone is $token Your available balance is KSh $currentCredits ^CON^EXTRA";  


}

}
if($LEVEL==41)
{
$statusCheck = "select credits from accounts where MSISDN = $MSISDN ";
$result = selectSQL($statusCheck, $DB_connect);
$noAccs = mysql_num_rows($result);
while($row = mysql_fetch_assoc($result))
			 { 
                               $currentCredits = $row["credits"];
                         }  
if ($INPUT==1)
{
$merchant ="Double M";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo Bus token to be used in $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo Bus token to be used in $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA";
 
}
else if ($INPUT==2) 
{
$merchant ="Citi Hoppa";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo Bus token to be used in $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN); 
$message= "0^Your Unga ya Leo Bus token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 
 
}
else if ($INPUT==3) 
{
$merchant ="KBS";
$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,'$merchant',now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo Bus token to be used in $merchant is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN);  
$message= "0^Your Unga ya Leo Bus token to be used at $merchant is $token Your available balance is KSh $currentCredits ^CON^EXTRA"; 

}
else if ($INPUT==4) 
{
$message= "42^Please Enter the Merchants phone number^CON^$currentCredits";   
}
else 
{
$message= "41^1.Double M\n2.Citi Hoppa\n3.KBS\n4.Matatu^CON^EXTRA";   
}
}


if($LEVEL==42)
{
$phone=$INPUT; 
$currentCredits=$EXTRA;
$checkstr = strlen($INPUT) ;
if(( !is_numeric($INPUT)) and (( $checkstr < 9) or ($checkstr > 12 ) ))
{
$message= "42^Please Enter a valid Merchants phone number^CON^$currentCredits";   
}
else
{
$checkNum = substr($phone, 0,4 );
if ($checkNum == 2547)
{
$phone = $phone;
} 
else
{
$phone = substr($phone,1);
$phone = 254 . $phone;
}

$token = rand(100000,999999);
$insertToken = "insert into tokens values ('',$token,$MSISDN,$phone,now(),0)";
$updtime = selectSQL($insertToken, $DB_connect);
$mes="Your Unga ya Leo Bus token to be used by $phone is $token Your available balance is KSh $currentCredits";
sendMessage($mes,$MSISDN); 
$message= "0^Your Unga ya Leo Bus token to be used by $phone is $token Your available balance is KSh $currentCredits ^CON^EXTRA";  
  
}
}



if($LEVEL==5)
{ 
$phone=$INPUT; 
$checkstr = strlen($INPUT) ;
if(( !is_numeric($INPUT)) and (( $checkstr < 9) or ($checkstr > 12 ) ))
{
$message="5^Enter phone number of a friend you want to tell about Unga ya Leo^CON^EXTRA";
}
else
{
$find = tellFriend($phone,$MSISDN,$keywordSearch);
if ($find == 'Yes')
{
$message="0^Thank You for recommending $phone to use Unga ya Leo^CON^EXTRA";
}
else if ($find == 'No')
{
$message="1^Sorry, You have already recommended Unga ya Leo to $phone^CON^EXTRA";
}
}
}

echo $message;

?>

