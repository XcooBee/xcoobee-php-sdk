<?php
namespace xcoobee\http;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use xcoobee\exceptions\GraphQLInvalidResponse;
use xcoobee\exceptions\GraphQLMissingData;
use xcoobee\core\Constants;
use xcoobee\auth\Auth;

class GraphQLClient extends Auth
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;
    
    public function __construct()
    {
        $log = new Logger('xcoobee');
        $log->pushHandler(new StreamHandler(__DIR__.'/xcoobee.log', Logger::DEBUG));
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $log,
                new MessageFormatter('Header:: {req_headers} --- HOST:: {url} --- REQ_BODY::{req_body} --- RESPONSE:: {res_body}')
                )
            );

        $this->guzzle = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => Constants::APIURL,
        ]);
    }
    
    /**
     * Make a GraphQL Request and get the raw guzzle response.
     *
     * @param string $query
     * @param array $variables
     * @param array $headers
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function raw($url, $query, $variables = [], $headers = [])
    {
        $endpoint = Constants::APIURL.$url;
        $headers["Authorization"] = Parent::getToken();
        
        return $this->guzzle->request('POST', $endpoint, [
            'json' => [
                'query' => $query,
                'variables' => $variables
            ],
            'headers' => $headers
        ], ['debug' => true]);
    }

    /**
     * Make a GraphQL Request and get the response body in JSON form.
     *
     * @param string $query
     * @param array $variables
     * @param array $headers
     * @param bool $assoc
     *
     * @return mixed
     *
     * @throws GraphQLInvalidResponse
     * @throws GraphQLMissingData
     */
    public function json($url, $query, $variables = [], $headers = [], $assoc = false)
    {
        $response = $this->raw($url, $query, $variables, $headers);
        $responseJson = json_decode($response->getBody(), $assoc);
        
        if ($responseJson === null) {
            throw new GraphQLInvalidResponse('GraphQL did not provide a valid JSON response. Please make sure you are pointing at the correct URL.');
        }

        return $responseJson;
    }
    /**
     * Make a GraphQL Request and get the guzzle response .
     *
     * @param string $query
     * @param array $variables
     * @param array $headers
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function response($url, $query, $variables = [], $headers = [])
    {
        $response = $this->raw($url, $query, $variables, $headers);
        return new Response($response);
    }
}