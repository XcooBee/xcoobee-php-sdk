<?php
require '../vendor/autoload.php';

use XcooBee\XcooBee;

// TODO: replace path with yours
$homeDir = '/home/vrabeshko/www/xcoobee-php-sdk';


$xcoobee = new XcooBee();

$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile($homeDir));
// or
//$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromData([
//    'apiKey'    => '',
//    'apiSecret' => '',
//]));

// Should be default campaign id provided in config
echo 'Requesting adding requests to campaign' . PHP_EOL;
$xcoobee->consents->requestConsent('~Volodymyr_Rabeshko');

echo 'Waiting for creating request (10s)' . PHP_EOL;
echo 'Requesting activating campaign' . PHP_EOL;
$xcoobee->consents->activateCampaign();
