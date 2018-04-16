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
    public function __construct(ResponseInterface $response)
    {
        $time = new \DateTime();
        $responseBody = json_decode($response->getBody());

        if (isset($responseBody->data)) {
            $this->data = $responseBody->data;
        }

        $this->errors = isset($responseBody->errors) ? $responseBody->errors : (object) [];

        $this->code = $response->getStatusCode();
        $this->time = $time->format('Y-m-d H:i:s');
    }
}
