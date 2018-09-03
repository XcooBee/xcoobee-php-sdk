<?php

namespace XcooBee\Http;

use Psr\Http\Message\ResponseInterface;

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
    
    /** @var mixed */
    public $request;
    
    /** @var Response */
    protected $_nextPage = null;
    
    /** @var Response */
    protected $_previousPage = null;

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
        if ($xcoobeeResponse->code === 200 && $xcoobeeResponse->errors) {
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
        return (bool) $this->_getNextPagePointer();
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
    public function getNextPage()
    {
        if ($this->hasNextPage()) {
            $request = clone $this->request;
            $endCursor = $this->_getNextPagePointer();
            $request->setVariables(['after' => $endCursor]);
            $nextPage = Self::setFromHttpResponse($request->makeCall());
            $this->setNextPage($nextPage);
            $nextPage->setPreviousPage($this);
            $nextPage->request = $request;
            
            return $nextPage;
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
        return $this->_previousPage;
    }
    
    public function setNextPage($nextResponse)
    {
        $this->_nextPage = $nextResponse;
    }
    
    public function setPreviousPage($previousResponse)
    {
        $this->_previousPage = $previousResponse;
    }

    protected function _getNextPagePointer()
    {
        foreach ($this->result as $result) {
            if(isset($result->page_info) && $result->page_info->has_next_page === true){
                return $result->page_info->end_cursor;
            }else{
                return null;
            }
        }
    }

}
