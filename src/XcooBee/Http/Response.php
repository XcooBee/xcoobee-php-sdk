<?php

namespace XcooBee\Http;


use Psr\Http\Message\ResponseInterface;

class Response
{
    /** @var object */
    public $data;

    /** @var object */
    public $errors;

    /** @var string */
    public $code;

    /** @var string */
    public $time;

    /**
     * Response constructor.
     *
     * @param ResponseInterface $response
     */
    public function __construct()
    {
		$time = new \DateTime();
        $this->time = $time->format('Y-m-d H:i:s');
        $errors = (object)[];
    }
    
	/*
	 * 
	 * name: Set response from http
	 * @param
	 * @return
	 * 
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
