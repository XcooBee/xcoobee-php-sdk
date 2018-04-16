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

echo 'Uploading files needed for bees' . PHP_EOL;
$xcoobee->bees->uploadFiles(['assets/cool.jpg', 'assets/nature.png']);

echo 'Take off provided bees' . PHP_EOL;
$xcoobee->bees->takeOff([
    'xcoobee_twitter_base' => ['message' => 'Test post'],
    'xcoobee_facebook_base' => ['message' => 'Test post'],
], [
    'process' => [
        'fileNames' => ['cool.jpg', 'nature.png'],
    ],
]);
