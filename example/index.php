<?php
require '../vendor/autoload.php';

use XcooBee\XcooBee;

$xcoobee = new XcooBee();

$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile('/home/vrabeshko/www/xcoobee-php-sdk'));

$res = $xcoobee->bees->listBees();

var_export($res);