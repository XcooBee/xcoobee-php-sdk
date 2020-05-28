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

$available = $xcoobee->system->getAvailableSubscriptions();

$subscription = $available->result->available_subscriptions[0];

$res = $xcoobee->system->addEventSubscriptions([
    [
        'topic' => $subscription->topic,
        'channel' => $subscription->channels[0],
        'handler' => 'testHandler',
    ]
]);

var_export($res->result);
