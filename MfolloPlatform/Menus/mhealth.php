<?php
$MSISDN = $_GET['MSISDN'];
$INPUT = $_GET['USSD_STRING'];
$LEVEL = $_GET['NEXT_LEVEL'];
$EXTRA = $_GET['EXTRA'];

$INPUT = strip_tags($INPUT);  // not neccssary for none HTML

$INPUT= trim(preg_replace("/\s+/"," ",$INPUT));

if($LEVEL==0)
 {
 $message="2^Welcome to Cohort Survey.Please enter your PIN^CON^EXTRA";
 }
if($LEVEL==2)
{
$trainerName=$INPUT;
$message="3^Have you had sex in the last 30 Days\n1.Yes\n2.No^CON^EXTRA";
}

if ($LEVEL==3)
{
if(($INPUT >= 1) and ($INPUT <= 2)) 
{
if ($INPUT == 1)
{
$PersonalNsensitive = 'Yes';
$message="4^How old was the sex partner\n1.Older\n2.Younger\n3.Same Age\n4.Dont Know^CON^$PersonalNsensitive";
}

if ($INPUT == 2)
{
$PersonalNsensitive = 'No';
$message="0^Thank You for taking part in the survey^END^EXTRA";
}
}
else
{
$message="3^Wrong Input!\nHave you had sex in the last 30 Days\n1.Yes\n2.No^CON^EXTRA";
}
}

if($LEVEL==4)
{
$mydata=$EXTRA;

if(($INPUT >= 1) and ($INPUT <= 4)) 
{

if ($INPUT == 1)
{
$rating = 'Older';
$message="5^How many years older^CON^$mydata*$rating";
}

if ($INPUT == 2)
{
$rating = 'Younger';
$message="5^How many years younger^CON^$mydata*$rating";
}

if ($INPUT == 3)
{
$rating = 'SameAge';
$message="10^Did you or your use a condom the last time you had sex\n1.Yes\n2.No^CON^$mydata*$rating";
}

if ($INPUT == 4)
{
$rating = 'DontKnow';
$message="10^Did you or your use a condom the last time you had sex\n1.Yes\n2.No^CON^$mydata*$rating";
}
}
else
{
$message="4^Wrong Input!\nHow old was the sex partner\n1.Older\n2.Younger\n3.Same Age\n4.Dont Know^CON^$mydata";
}
}


if ($LEVEL==5)
{
$mydata=$EXTRA;
if(($INPUT >= 1) and ($INPUT <= 100)) 
{
$age=$INPUT;
$message="10^Did you or your use a condom the last time you had sex\n1.Yes\n2.No^CON^$mydata*$age";
}
else
{
$message="5^Wrong Input!\nHow many years younger or older was your sex partner^CON^$mydata";
}
}

if($LEVEL==10)
{
$mydata=$EXTRA;
$feedback=$INPUT;
if(($INPUT >= 1) and ($INPUT <= 2)) 
{
if ($INPUT == 1)
{
$rating = 'UsedCondom';
$message="0^Thank You for taking part in the survey^END^EXTRA";
}
if ($INPUT == 2)
{
$rating = 'NoCondom';
$message="0^Thank You for taking part in the survey^END^EXTRA";
}
}
else 
{
$message="10^Wrong Input!\nDid you or your use a condom the last time you had sex\n1.Yes\n2.No^CON^$mydata";
}
}

echo $message;
?>
