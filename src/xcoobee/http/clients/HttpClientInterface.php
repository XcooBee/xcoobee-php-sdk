<?php
namespace xcoobee\http\clients;

/**
 * HttpClientInterface
 */
interface HttpClientInterface
{
    /**
     * Send request to the server and fetch the raw response
     *
     * @param  string $url     URL/Endpoint to send the request to
     * @param  string $method  Request Method
     * @param  string|resource|\Psr\Http\Message\StreamInterface|null $body Request Body
     * @param  array  $headers Request Headers
     * @param  array  $options Additional Options
     *
     * @return \xcoobee\http\RawResponse Raw response from the server
     *
     * @throws \xcoobee\exceptions\ClientException
     */
    public function send($url, $method, $body, $headers = [], $options = []);
}
