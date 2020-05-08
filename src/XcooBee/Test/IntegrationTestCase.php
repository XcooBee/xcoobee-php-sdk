<?php

namespace XcooBee\Test;

use XcooBee\Test\TestCase;
use XcooBee\XcooBee;

abstract class IntegrationTestCase extends TestCase {

    /** @var XcooBee */
    public static $xcoobee;

    /** @var consentId */
    public static $consentId;

    /** @var campaign */
    public static $campaign;

}
