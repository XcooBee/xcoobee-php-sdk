<?php
namespace XcooBee\Core;
use XcooBee\Models\ConfigModel;
use XcooBee\Store\PersistedData;
use XcooBee\Core\Constants;
use Psr\Cache\CacheItemPoolInterface;

class Configuration
{
    /**
     * Cache
     *
     * @var xcoobee\store\PersistedData
     */
    private $store; 

    public function __construct(){
        $this->store = new PersistedData();
    }

    public function setConfig(ConfigModel $config)
    {
        $this->store = new PersistedData();
        $this->store->setStore(Constants::CURRENT_CONFIG, $config);

        $currentConfig = $this->store->getStore(Constants::CURRENT_CONFIG);
        $previousConfig = $this->store->getStore(Constants::PREVIOUS_CONFIG);
        
        if(($currentConfig != $previousConfig) || ($previousConfig == null))
        {
            $this->store->setStore(Constants::CURRENT_CONFIG, $config);
            $this->store->setStore(Constants::PREVIOUS_CONFIG, $config);

            return true;
        }
        else{
            return true;
        }
    }

    public function getConfig($key){
        $this->store = new PersistedData();
        return $this->store->getStore($key);
    }

    public function clearConfig()
    {
        $this->store = new PersistedData();
        $this->store->clearStore();
    }

    public function defaultConfig($homedir){
        $this->store = new PersistedData();
        $currentConfig = $this->store->getStore(Constants::CURRENT_CONFIG);
        
        if($currentConfig == null){
            $lines = file_get_contents($homedir.Constants::CONFIG_FILE);
            $lines = preg_split("/\\r\\n|\\r|\\n/", $lines);
            $configModel = new ConfigModel;

            $configArray=[];
            foreach($lines as $line)
            {
                $column = split("=", $line);
                $configArray[$column[0]] = $column[1];
            }
            
            $configModel->apiKey = $configArray["key"];
            $configModel->apiSecret = $configArray["secret"];
            $configModel->pgpPassword = $configArray["pgppass"];
            $configModel->campaignId = $configArray["campaign_id"];
            $configModel->encode = $configArray["encode"];
            
            $this->store->setStore(Constants::CURRENT_CONFIG, $configModel);
            $this->store->setStore(Constants::PREVIOUS_CONFIG, $configModel);
            
            return  $this->store->getStore(Constants::CURRENT_CONFIG);
        }
    }
}
?>