<?php
require '../vendor/autoload.php';

use XcooBee\XcooBee;

$xcoobee = new XcooBee();

$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromData([
    'apiKey'        => 'zoKy4WkQ4JKYVLWN73JN4ha3E43sEq',
    'apiSecret'     => 'EGufpI+45P0f1UnZtKjVreWmOnt99t',
    'encode'        => false,
]));

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

$xcoobee->bees->takeOff($bees, $parameters);
