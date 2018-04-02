<?php namespace xcoobee;

use xcoobee\core\Configuration;
use xcoobee\models\ConfigModel;
use xcoobee\auth\Auth;

use xcoobee\core\Bees;
use xcoobee\core\Users;

class XcooBee
{
    
    /**
     * Config Object
     *
     * @var Configuration
     */
    private $config;

    /**
     * XcooBeeClient expects user's home directory path for config and secret.
     *
     * @param string $homeDir
     */
    public function __construct($homeDir){
        $this->config = new Configuration;
        $this->config->defaultConfig($homeDir);
    }

    /**
     * setConfig override default configuration.
     *
     * @param string $key
     * @param string $secret
     * @param string $pgpsecret
     * @param string $pgppass
     * @param string $campaign_id
     * @param int $encode
     * @return bool
     */
    public function setConfig($key, $secret, $pgpsecret, $pgppass, $campaign_id, $encode)
    {
        $config = new ConfigModel;
        $config->apiKey = $key;
        $config->apiSecret = $secret;
        $config->pgpSecret = $pgpsecret;
        $config->pgpPassword = $pgppass;
        $config->campaignId = $campaign_id;
        $config->encode = $encode;
        
        return $this->config->setConfig($config);
    }

    public function getConfig($key)
    {
        return $this->config->getConfig($key);
    }

    /**
     * clearConfig clears configuration from memory/cache.
     *
     * @return bool
     */
    public function clearConfig()
    {
        return $this->config->clearConfig();
    }

    public function listBees($searchText = "")
    {
        $bees = new Bees;
        return $bees->getBees($searchText);
    }

    public function uploadFiles($files, $endPoint = null)
    {
        $bees = new Bees;
        return $bees->uploadFiles($files, $endPoint);
    }

    public function testXcoobee()
    {
        $this->auth = new uploadFiles;
        return $this->auth->uploadFiles("asdfadsf");
        //$users = new Users;
        //return $users->getUser();
    }
}