<?php
require '../vendor/autoload.php';

use XcooBee\XcooBee;

$xcoobee = new XcooBee();

$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile('/var/www/html'));

$xcoobee->bees->uploadFiles(['1.jpg', '2.jpg']);

$bees = [
    'xcoobee_twitter_base' => [
        'message' => 'test',
    ],
];
$parameters = [
    'process' => [
        'fileNames' => ['1.jpg', '2.jpg'],
    ]
];

$res = $xcoobee->bees->takeOff($bees, $parameters);

var_export($res);