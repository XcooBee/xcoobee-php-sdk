<?php

namespace Test\XcooBee\Models;


use XcooBee\Models\ConfigModel as Config;
use XcooBee\Test\TestCase;

class ConfigModel extends TestCase
{
    public function testCreateFromData_AllDataPassed()
    {
        $config = Config::createFromdata([
            'apiKey'        => 'testKey',
            'apiSecret'     => 'testSecret',
            'pgpSecret'     => 'testPgpSecret',
            'pgpPassword'   => 'testPgpPass',
            'campaignId'    => 'testCampaign',
            'encode'        => true,
        ]);

        $this->assertEquals('testKey', $config->apiKey);
        $this->assertEquals('testSecret', $config->apiSecret);
        $this->assertEquals('testPgpSecret', $config->pgpSecret);
        $this->assertEquals('testPgpPass', $config->pgpPassword);
        $this->assertEquals('testCampaign', $config->campaignId);
        $this->assertTrue($config->encode);
    }

    public function testCreateFromData_PartialDataPassed()
    {
        $config = Config::createFromdata([
            'apiKey'        => 'testKey',
            'apiSecret'     => 'testSecret',
            'campaignId'    => 'testCampaign',
            'encode'        => false,
        ]);

        $this->assertEquals('testKey', $config->apiKey);
        $this->assertEquals('testSecret', $config->apiSecret);
        $this->assertEquals('testCampaign', $config->campaignId);
        $this->assertNull($config->pgpSecret);
        $this->assertNull($config->pgpPassword);
        $this->assertFalse($config->encode);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testCreateFromData_KeyMissed()
    {
        Config::createFromData(['apiSecret' => 'test']);
    }

    public function testCreateFromFile_GetConfigAndPgpSecret()
    {
        $config = Config::createFromFile(__DIR__ . '/../../../assets/valid-config-url');

        $this->assertEquals('testKey', $config->apiKey);
        $this->assertEquals('testSecret', $config->apiSecret);
        $this->assertEquals('testCampaign', $config->campaignId);
        $this->assertEquals('testPgpSecret', $config->pgpSecret);
        $this->assertEquals('testPgpPass', $config->pgpPassword);
        $this->assertTrue($config->encode);
    }

    public function testCreateFromFile_GetConfigWithoutPgpSecret()
    {
        $config = Config::createFromFile(__DIR__ . '/../../../assets/valid-config-without-phpsecret');

        $this->assertEquals('testKey', $config->apiKey);
        $this->assertEquals('testSecret', $config->apiSecret);
        $this->assertEquals('testCampaign', $config->campaignId);
        $this->assertEquals('testPgpPass', $config->pgpPassword);
        $this->assertNull($config->pgpSecret);
        $this->assertFalse($config->encode);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testCreateFromFile_ConfigFileNotFound()
    {
        Config::createFromFile(__DIR__ . '/../../../assets');
    }
}