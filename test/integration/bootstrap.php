<?php

use XcooBee\Exception\XcooBeeException;
use XcooBee\XcooBee;

require_once( __dir__ . '/../../vendor/autoload.php');
if (!file_exists(__dir__ . '/assets/config/.xcoobee/config')) {
    throw new XcooBeeException('Please create your config file in direcotry' . __dir__ . '/assets/config/.config/config');
}

global $consentId;
global $xcoobee;

$xcoobee = new XcooBee();
$xcoobee->clearConfig();
$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile(__DIR__ . '/assets/config'));

$consents = $xcoobee->consents->listConsents();
if ($consents) {
    throw new XcooBeeException('No consents fond');
}

$firstConsent = end($consents->result->consents->data);
$consentId = $firstConsent->consent_cursor;
?>
