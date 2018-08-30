<?php

namespace XcooBee\Http;

use XcooBee\Http\Client;
use XcooBee\XcooBee;

class Request
{

    /** @var mixed */
    protected static $_data;

    /** @var string */
    protected static $_uri;

    /** @var string */
    protected static $_query;

    /** @var mixed */
    protected static $_headers;

    /** @var string */
    protected static $_variabels;

    /** @var mixed */
    protected $_client;

    public function __construct($uri = null)
    {
        if ($uri) {
            self::$_uri = $uri;
        }

        $xcoobee = new XcooBee();
        $this->_client = new Client($xcoobee);
    }

    /**
     * 
     * name: make http call 
     * 
     * @param array $data
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeCall($data)
    {
        if (array_key_exists('headers', $data)) {
            $this->setHeaders($data['headers']);
        }

        if (array_key_exists('json', $data)) {
            if (array_key_exists('variables', $data['json'])) {
                $this->setVariables($data['json']['variables']);
            }

            if (array_key_exists('query', $data['json'])) {
                $this->setQuery($data['json']['query']);
            }
        }

        $response = $this->_client->post(self::$_uri, $data);

        return $response;
    }

    /**
     * 
     * name: get data 
     *
     */
    public function getHeaders()
    {
        return self::$_headers;
    }

    /**
     * 
     * name: set headers
     *
     */
    public function setHeaders($headers)
    {
        self::$_headers = $headers;
    }

    /**
     * 
     * name: set Variables
     *
     */
    public function setVariables($variables)
    {
        self::$_variabels = $variables;
    }

    /**
     * 
     * name: get data 
     *
     */
    public function getVariables()
    {
        return self::$_variabels;
    }

    /**
     * 
     * name: set query 
     *
     */
    public function setQuery($query)
    {
        self::$_query = $query;
    }

    /**
     * 
     * name: get query 
     *
     */
    public function getQuery()
    {
        return self::$_query;
    }
    
    /**
     * 
     * name: get query 
     * @param: array $variables
     * 
     * @return mixed Data
     */
    public function getData($variables)
    {
        $data = [];
        $data['headers'] = $this->getHeaders();
        $data['json']['query'] = $this->getQuery();
        $data['json']['variables'] = array_merge($this->getVariables(), $variables);

        return $data;
    }

}
