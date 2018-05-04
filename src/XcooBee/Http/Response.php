<?php

namespace XcooBee\Http;


use Psr\Http\Message\ResponseInterface;

class Response
{
    /** @var object */
    public $data = null;

    /** @var object */
    public $errors = [];

    /** @var string */
    public $code;

    /** @var string */
    public $time;

    /**
     * Response constructor.
     *
     */
    public function __construct()
    {
		$time = new \DateTime();
        $this->time = $time->format('Y-m-d H:i:s');
    }
    
	/*
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
            $xcoobeeResponse->data = $responseBody->data;
        }
		
        if (isset($responseBody->errors)) {
		  $xcoobeeResponse->errors = $responseBody->errors;
		}

        $xcoobeeResponse->code = $response->getStatusCode();

        return $xcoobeeResponse;
	}
}
