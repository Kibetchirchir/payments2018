
<?php

  $username = "evidsibi@gmail.com";
        $password ="mfollo'z";
        $RequestArray = array("c68" => strtoupper("SZH091"));

        $JsonRequest = json_encode($RequestArray);
echo $JsonRequest;
       $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://54.195.217.120/api/record-sets/2/records?query=".rawurlencode($JsonRequest));
curl_setopt($ch, CURLOPT_POST, 1);
  // curl_setopt($ch, CURLOPT_POSTFIELDS, "query=".rawurlencode($JsonRequest));
//curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_VERBOSE , 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');        
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $output = curl_exec($ch) . curl_error($ch);
//        echo curl_error($ch);
print_r(curl_getinfo($ch));
        curl_close($ch);
//echo curl_error($ch);

        $ResponseArray = json_decode($output);
print_r($ResponseArray);

print_r($ResponseArray->records); 
$da = $ResponseArray->records;
print($da[0]->c2);

print($ResponseArray->count);
//$Details =json_decode($ResponseArray->records,true);

