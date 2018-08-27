<?php

namespace XcooBee\Http;

use XcooBee\Http\Client;
use XcooBee\XcooBee;

class Request
{

    protected static $_data;
    protected static $_uri;
    
    
    public function __construct($uri = null) 
    {
        if($uri){
            self::$_uri = $uri;
        }
    }
    
    /**
     * 
     * name: make https call 
     * 
     * @param array $data
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeCall($data)
    {
        self::setData($data);
        $xcoobee = new XcooBee();
        $client = new Client($xcoobee);
        $response = $client->post(self::$_uri, $data);

        return $response;
    }
    
    /**
     * 
     * name: get data 
     *
     */
    public function getData()
    {
        return self::$_data;
    }
    
    /**
     * 
     * name: set headers
     *
     */
    public function setHeaders($headers)
    {
        
    }
    
    /**
     * 
     * name: set Data
     *
     */
    public function setData($data)
    {
        self::$_data = $data;
    }
}
