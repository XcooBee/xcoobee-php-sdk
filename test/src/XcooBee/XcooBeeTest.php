<?php

namespace Test\XcooBee;

use XcooBee\Models\ConfigModel;
use XcooBee\Test\TestCase;

class XcooBee extends TestCase
{
    /** @var \XcooBee\XcooBee */
    protected $_instance;

    protected function setUp()
    {
        $this->_instance = new \XcooBee\XcooBee();

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->_instance->clearConfig();
    }

    public function testGetConfig()
    {
        $this->_instance->setConfig(ConfigModel::createFromData([
            'apiKey'        => 'testKey',
            'apiSecret'     => 'testSecret',
            'pgpSecret'     => 'testPgpSecret',
            'pgpPassword'   => 'testPgpPass',
            'campaignId'    => 'testCampaign',
            'encode'        => true,
        ]));

        /** @var ConfigModel $config */
        $config = $this->_instance->getConfig();

        $this->assertEquals('testKey', $config->apiKey);
        $this->assertEquals('testSecret', $config->apiSecret);
        $this->assertEquals('testPgpSecret', $config->pgpSecret);
        $this->assertEquals('testPgpPass', $config->pgpPassword);
        $this->assertEquals('testCampaign', $config->campaignId);
        $this->assertTrue($config->encode);
    }
}