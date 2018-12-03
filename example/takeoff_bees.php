<?php
require '../vendor/autoload.php';

use XcooBee\XcooBee;

$xcoobee = new XcooBee();

// Set configuration from the config file located at your home directory.
//  e.g. /home/user/.xcoobee/config on POSIX-compliant systems,
//  or /Users/MyUserDir/.xcoobee/config on Windows
$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile());

// Or, provide configuration data directly.
// $xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromData([
//     'apiKey'    => '',
//     'apiSecret' => '',
// ]));

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
