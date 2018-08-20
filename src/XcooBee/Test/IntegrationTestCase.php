<?php

namespace XcooBee\Test;

use XcooBee\Test\TestCase;
use XcooBee\XcooBee;

abstract class IntegrationTestCase extends TestCase {

    /** @var XcooBee */
    public static $xcoobee;

    /** @var consentId */
    public static $consentId;

    public function __construct()
    {
        global $xcoobee;
        global $consentId;
        self::$xcoobee = $xcoobee;
        self::$consentId = $consentId;
    }

}
