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

echo 'Requesting creating campaign' . PHP_EOL;
$xcoobee->consents->createCampaign([
    'name' => 'new campaign 2',
    'title' => [
        [
            'locale' => 'en-us',
            'value' => 'test',
        ],
    ],
    'description' => [
        [
            'locale' => 'en-us',
            'value' => 'test',
        ],
    ],
    'requests' => [
        [
            'name' => 'test',
            'request_data_types' => ['first_name', 'last_name', 'xcoobee_id'],
            'required_data_types' => ['first_name', 'last_name', 'xcoobee_id'],
            'consent_types' => ['deliver_a_product'],
        ]
    ],
]);

echo 'Waiting for creating campaign (20s)' . PHP_EOL;
sleep(20);

$res = $xcoobee->consents->listCampaigns();

var_export($res);
