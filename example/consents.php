<?php
require '../vendor/autoload.php';

use XcooBee\XcooBee;

$xcoobee = new XcooBee();

$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile('/home/vrabeshko/www/xcoobee-php-sdk'));

$res = $xcoobee->consents->createCampaign([
    'name' => 'test',
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
