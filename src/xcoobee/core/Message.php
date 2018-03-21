<?php namespace xcoobee\core;

use xcoobee\models\ConfigModel;
use xcoobee\core\Configuration;

class Message
{
    public function sendUserMessage($message, $consentId, ConfigModel $config)
    {
        if($this->validate($message, $consentId, $config))
        {
            if($config !== null){
                $this-> validateConfig($config);
            }
            else {
                //get config from home directory
    
                //
            }
            return "hello ".$message;
        }
    }

    protected function validate($message, $consentId, ConfigModel $config)
    {
        if(empty($message) or empty($consentId)){
            throw new \InvalidArgumentException("message, and consentId is required");
            return false;
        }

        return true;
    }

    protected function validateConfig(ConfigModel $config)
    {
        return ($config->apiKey !== "" && $config->apiSecret !== "" && $config->pgpSecret !== "" && $config->pgpPassword !== "");
    }
}