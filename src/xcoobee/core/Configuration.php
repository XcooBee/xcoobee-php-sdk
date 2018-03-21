<?php
namespace xcoobee\core;
use xcoobee\models\ConfigModel;

class Configuration
{
    public function setConfig(ConfigModel $config)
    {
        //set config.
    }

    public function clearConfig()
    {
        //clear config.
    }

    public function findConfig(ConfigModel $config)
    {
        $currentConfig = $config;
        $previousConfig = $this->getConfig();

    }

    public function getConfig()
    {

    }

    protected function compareConfig(ConfigModel $previous, ConfigModel $current)
    {
        if($previous !== null && $current !== null){
            if(($previous->apiKey === $current->apiKey) && ($previous->apiSecret === $current->apiSecret) && ($previous->pgpSecret === $current->pgpSecret) && ($previous->pgpPassword === $current->pgpPassword))
            {
                return $current;
            }
            else{
                $this->setConfig($current);
            }
        }
    }
}
?>