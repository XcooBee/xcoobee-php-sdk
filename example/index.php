<?php 
require "../vendor/autoload.php";
use xcoobee\xcoobee;

$xcoobee = new XcooBee("C:\Users\Administrator");

//$xcoobee->setConfig("xcoobeeKey", "secretkey", "pgpsecret", "pgppass", "campaignid");
// $xcoobee->setConfig("xcoobeeKey", "secretkey1", "pgpsecret", "pgppass", "campaignid",1);
// $config =  $xcoobee->getConfig("CURRENT_CONFIG");

//$xcoobee->clearConfig();
//$config =  $xcoobee->getConfig("CURRENT_CONFIG");

//echo $config->apiKey;

$auth = $xcoobee->uploadFiles(
    [
        "C:\Users\Administrator\Desktop\\test.txt",
        "C:\Users\Administrator\Desktop\\test2.txt",
        "C:\Users\Administrator\Desktop\\testingtimezone.jpg"
    ], "outbox");

?>



