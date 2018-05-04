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

$res = $xcoobee->bees->listBees();

var_export($res);
