<?php 
require "../vendor/autoload.php";
use xcoobee\xcoobee;

$xcoobee = new XcooBee("C:\Users\Administrator");

//$xcoobee->setConfig("xcoobeeKey", "secretkey", "pgpsecret", "pgppass", "campaignid");
// $xcoobee->setConfig("xcoobeeKey", "secretkey1", "pgpsecret", "pgppass", "campaignid",1);
// $config =  $xcoobee->getConfig("CURRENT_CONFIG");
//var_dump($config);
//$xcoobee->clearConfig();
//$config =  $xcoobee->getConfig("CURRENT_CONFIG");
//var_dump($config);
//echo $config->apiKey;

$auth = $xcoobee->listBees();
echo $auth->data;

?>



