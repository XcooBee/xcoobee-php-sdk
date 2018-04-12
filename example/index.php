<?php 
require "../vendor/autoload.php";

use XcooBee\XcooBee;

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
    ]);


$bees = array(
);

$subscriptions = array(
    'process' => array(
        'target' => "https://mysite.com/beehire/notification/",
        "signed" => true,
        "events" => "error,success,deliver,present,download,delete,reroute"
    )
);

$parameters = array(
    'process'=>array(
        'fileNames' => array("test.txt"),
        'userReference'=>'myownpreference',
        "destinations" => array("sunder81@gmail.com", "~Sunder_Free")
    )
);

$takeOff = $xcoobee->takeOff($bees, $parameters, $subscriptions);

?>



