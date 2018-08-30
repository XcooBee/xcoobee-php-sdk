<?php

namespace XcooBee\Http;

use Psr\Http\Message\ResponseInterface;
use XcooBee\Http\Request;

class Response
{

    /** @var mixed */
    public $result = null;

    /** @var object */
    public $errors = [];

    /** @var string */
    public $code;

    /** @var string */
    public $time;
    
    /** @var string */
    public $request_id;
    
    /** @var string */
    static $endCursor;
    
    /** @var mixed */
    static $response;
    
    public function __construct() 
    {
        $time = new \DateTime();
        $this->time = $time->format('Y-m-d H:i:s');
    }

    /**
     * 
     * name: Set response data from http request
     * 
     * @param ResponseInterface $response
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function setFromHttpResponse(ResponseInterface $response) 
    {
        $xcoobeeResponse = new self();
        $responseBody = json_decode($response->getBody());

        if (isset($responseBody->data)) {
            $xcoobeeResponse->result = $responseBody->data;
        }
        
        $xcoobeeResponse->request_id = $responseBody->request_id;
        
        if (isset($responseBody->errors)) {
            $xcoobeeResponse->errors = $responseBody->errors;
        }

        $xcoobeeResponse->code = $response->getStatusCode();
        if($xcoobeeResponse->code === 200 && $xcoobeeResponse->errors){
            $xcoobeeResponse->code = 400;
        }
        
        return $xcoobeeResponse;
    }
    
    /**
     * 
     * name: has next page
     * 
     * @param ResponseInterface $resultObject
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function hasNextPage()
    {
        foreach(self::$response->result as $result){
            self::$endCursor = $result->page_info->end_cursor;
            return $result->page_info->has_next_page;
        }
    }
    
    /**
     * 
     * name: get next page data
     * 
     * @param ResponseInterface $resultObject
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNextPage($resultObject)
    {
        self::$response = $resultObject;
        $request = new Request();
        if($this->hasNextPage($resultObject)){
            $data = $request->getData(['after' => self::$endCursor]);

            return Self::setFromHttpResponse($request->makeCall($data));
        }
        
        return null;
    }
    
    /**
     * 
     * name: get previous page data
     * 
     * @param ResponseInterface $resultObject
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPreviousPage()
    {
        if(self::$response){
            return self::$response;
        }
        
        return null;
    }
}
