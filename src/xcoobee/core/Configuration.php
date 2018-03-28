<?php
namespace xcoobee\core;
use xcoobee\models\ConfigModel;
use xcoobee\store\PersistedData;
use xcoobee\core\Constants;
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
        //var_dump($config->apiKey);
        $this->store = new PersistedData();
        $this->store->setStore(Constants::CURRENT_CONFIG, $config);

        $currentConfig = $this->store->getStore(Constants::CURRENT_CONFIG);
        $previousConfig = $this->store->getStore(Constants::PREVIOUS_CONFIG);
        
        if(($currentConfig != $previousConfig) || ($previousConfig == null))
        {
            echo "config null or new";
            $this->store->setStore(Constants::CURRENT_CONFIG, $config);
            $this->store->setStore(Constants::PREVIOUS_CONFIG, $config);

            return true;
        }
        else{
            echo "SAME";
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
        //var_dump($currentConfig);
        if($currentConfig == null){
            //echo "getting config";
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