<?php

use XcooBee\XcooBee;
use XcooBee\Test\IntegrationTestCase;

require_once( __dir__ . '/../../vendor/autoload.php');
if (!file_exists(__dir__ . '/assets/config/.xcoobee/config')) {
    die('Please create your config file in direcotry' . __dir__ . '/assets/config/.xcoobee/config');
}

$xcoobee = new XcooBee();
$xcoobee->clearConfig();
$xcoobee->setConfig(\XcooBee\Models\ConfigModel::createFromFile(__DIR__ . '/assets/config'));

$consents = $xcoobee->consents->listConsents();
if (!$consents) {
    die('No consent found');
}

$campaign = $xcoobee->consents->getCampaignInfo();

IntegrationTestCase::$xcoobee = $xcoobee;
IntegrationTestCase::$consentId = end($consents->result->consents->data)->consent_cursor;
IntegrationTestCase::$campaign = $campaign->result->campaign;
