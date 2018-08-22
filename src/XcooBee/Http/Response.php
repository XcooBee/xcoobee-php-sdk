<?php

namespace XcooBee\Http;

use Psr\Http\Message\ResponseInterface;
use XcooBee\XcooBee;

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
    
    static $requestId;

    static $hasNextPage;
    
    static $endCursor;
    
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
    
    public function hasNextPage($resultObject)
    {
        self::$requestId = $resultObject->request_id;
        
        foreach($resultObject->result as $result){
            self::$endCursor = $result->page_info->end_cursor;
            self::$hasNextPage = $result->page_info->has_next_page;
     
            return $result->page_info->has_next_page;
        }
    }
    
    public function getNextPage($resultObject)
    {
        if($this->hasNextPage($resultObject)){
            $xcoobee = new XcooBee();
            $variables = $resultObject->request->getVariables();
            $query = $resultObject->request->getQuery();
            $config = $resultObject->request->getConfig();
            $variables['after'] = self::$endCursor;
            return $xcoobee->request->makeCall($query, $variables, $config);
            //return $xcoobee->users->getConversations(1, self::$requestId);
        }
        
        return null;
    }
    
    public function getPreviousPage($resultObject)
    {
        if(self::$hasNextPage){
            
        }
        
        return null;
    }
}
