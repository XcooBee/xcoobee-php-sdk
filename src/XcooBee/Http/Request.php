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

    /** @var Client */
    protected $_client;

    public function __construct($uri)
    {
        $this->_uri = $uri;
        $this->_client = new Client(new XcooBee());
    }

    /**
     * make http call 
     * 
     * @return \GuzzleHttp\Psr7\Response
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
     * get Headers 
     *
     * @return array headers
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * set headers
     *
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * set Variables
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
     * get data 
     *
     * @return array Variables
     */
    public function getVariables()
    {
        return $this->_variabels;
    }

    /**
     * set query 
     *
     * @return void
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * get query 
     *
     * @return String
     */
    public function getQuery()
    {
        return $this->_query;
    }

}
