<?php

namespace XcooBee\Http;

use XcooBee\Http\Client;
use XcooBee\XcooBee;

class Request
{

    /** @var string */
    protected $_uri;

    /** @var string */
    protected $_query;

    /** @var array */
    protected $_headers;

    /** @var array */
    protected $_variabels;

    /** @var mixed */
    protected $_client;

    public function __construct($uri)
    {
        $this->_uri = $uri;
        $this->_client = new Client(new XcooBee());
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
    public function makeCall()
    {
        $response = $this->_client->post($this->_uri, [
            'json' => [
                'query' => $this->getQuery(),
                'variables' => $this->getVariables()
            ],
            'headers' => $this->getHeaders()
        ]);

        return $response;
    }

    /**
     * 
     * name: get data 
     *
     * @return mixed headers
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * 
     * name: set headers
     *
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * 
     * name: set Variables
     *
     * @return void
     */
    public function setVariables($variables)
    {
        if ($this->getVariables()) {
            $this->_variabels = array_merge($this->getVariables(), $variables);
        } else {
            $this->_variabels = $variables;
        }
    }

    /**
     * 
     * name: get data 
     *
     * @return array Variables
     */
    public function getVariables()
    {
        return $this->_variabels;
    }

    /**
     * 
     * name: set query 
     *
     * @return void
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * 
     * name: get query 
     *
     * @return String
     */
    public function getQuery()
    {
        return $this->_query;
    }

}
