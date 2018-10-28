<?php
date_default_timezone_set("Africa/Nairobi");

$flogPath = 'sdptests.log';

function flog($file, $string) {

    $date = date("Y-m-d G:i:s");

    if ($fo = fopen($file, 'ab')) {
        fwrite($fo, "$date | $string\n");
        fclose($fo);
    } else {
        
    }
}

?>
