<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$file = fopen("mails.txt","r");

while(! feof($file)) {
  $l = trim(fgets($file));
$start = strpos($l, '<');
if(!$start) {
 $start = 21;
 $end = 1000;
} else {
$end = strpos($l, '>');
}

$email = trim(substr($l, $start+1, $end-$start-1));
print $email."\n";  
}

fclose($file);
