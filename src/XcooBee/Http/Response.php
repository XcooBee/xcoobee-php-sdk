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

    /** @var Request */
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
     * Set response data from http request
     * 
     * @param ResponseInterface $response
     * 
     * @return \XcooBee\Http\Response
     */
    public static function setFromHttpResponse(ResponseInterface $response)
    {
        $xcoobeeResponse = new self();
        $responseBody = json_decode($response->getBody());

        if (isset($responseBody->data)) {
            $xcoobeeResponse->result = $responseBody->data;
        }
        
        if(isset($responseBody->request_id)) {
            $xcoobeeResponse->request_id = $responseBody->request_id;
        }
        
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
     * Check if Response is iterables and has next page data
     * 
     * @return bool 
     * 
     */
    public function hasNextPage()
    {
        return (bool) $this->_getNextPagePointer();
    }

    /**
     * Returns next page response
     * 
     * @return \XcooBee\Http\Response
     */
    public function getNextPage() 
    {
        if ($this->hasNextPage()) {
            $request = clone $this->request;
            $endCursor = $this->_getNextPagePointer();
            $request->setVariables(['after' => $endCursor]);
            $nextPage = self::setFromHttpResponse($request->makeCall());
            $this->setNextPage($nextPage);
            $nextPage->setPreviousPage($this);
            $nextPage->request = $request;

            return $nextPage;
        }

        return null;
    }

    /**
     * Returns previous page response
     * 
     * @return \XcooBee\Http\Response
     */
    public function getPreviousPage()
    {
        return $this->_previousPage;
    }
    
    /**
     * set next page response
     *
     * @param Response $nextResponse
     * 
     * @return void
     */
    public function setNextPage($nextResponse)
    {
        $this->_nextPage = $nextResponse;
    }
    
    /**
     * set previous page response
     *
     * @param Response $previousResponse
     * 
     * @return void
     */
    public function setPreviousPage($previousResponse)
    {
        $this->_previousPage = $previousResponse;
    }
    
    /**
     * returns next page pointer
     *
     * @return String
     */
    protected function _getNextPagePointer()
    {
        foreach ($this->result as $result) {
            if (isset($result->page_info) && $result->page_info->has_next_page === true) {
                return $result->page_info->end_cursor;
            } else {
                return null;
            }
        }
    }

}
