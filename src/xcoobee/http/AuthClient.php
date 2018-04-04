<?php namespace XcooBee\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use XcooBee\Core\Constants;

class AuthClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct()
    {
        $log = new Logger('xcoobee');
        $log->pushHandler(new StreamHandler(__DIR__.'/auth.log', Logger::DEBUG));
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $log,
                new MessageFormatter('Header:: {req_headers} --- HOST:: {url} --- REQ_BODY::{req_body} --- RESPONSE:: {res_body}')
                )
            );

        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => Constants::APIURL,
            // You can set any number of default request options.
            'timeout'  => Constants::TIME_OUT,
            'handler' => $stack,
        ]);
    }

    public function request($uri, $data)
    {
        try {
            return $this->client->request('POST', $uri, ['body'=>$data]);
        } catch (RequestException $e) {
            throw AuthException::fromRequestException($e);
        } catch (\Throwable $e) {
            throw new AuthException($e->getMessage(), $e->getCode(), $e);
        }
    }
}