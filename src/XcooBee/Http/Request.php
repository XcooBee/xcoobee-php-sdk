<?php

namespace XcooBee\Http;

use XcooBee\Http\GraphQLClient;
use XcooBee\XcooBee;

class Request
{

    protected $config;
    protected $query;
    protected $variables;
    
    public function makeCall($query, $variables = [], $config = [])
    {
        $this->config = $config;
        $this->query = $query;
        $this->variables = $variables;
        $client = new GraphQLClient(new XcooBee());
        if($config){
            $config = \XcooBee\Models\ConfigModel::createFromData($config);
        }
        $response = $client->request($query, $variables, [], $config);
        $response->request = $this;

        return $response;
    }
    
    public function getQuery() 
    {
       return $this->query; 
    }
    
    public function getVariables()
    {
        return $this->variables;
    }
    
    public function getConfig() 
    {
       return $this->config; 
    }
}
