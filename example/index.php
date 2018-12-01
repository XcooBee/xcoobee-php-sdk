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

$res = $xcoobee->system->ping();

var_export($res);
