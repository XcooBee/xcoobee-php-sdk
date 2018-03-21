<?php
namespace xcoobee\http\clients;

use InvalidArgumentException;
use GuzzleHttp\Client as Guzzle;

/**
 * HttpClientFactory
 */
class HttpClientFactory
{
    /**
     * Make HTTP Client
     *
     * @param  \xcoobee\http\clients\HttpClientInterface|\GuzzleHttp\Client|null $handler
     *
     * @return \xcoobee\http\clients\HttpClientInterface
     */
    public static function make($handler)
    {
        //No handler specified
        if (!$handler) {
            return new GuzzleHttpClient();
        }

        //Custom Implementation, maybe.
        if ($handler instanceof HttpClientInterface) {
            return $handler;
        }

        //Handler is a custom configured Guzzle Client
        if ($handler instanceof Guzzle) {
            return new GuzzleHttpClient($handler);
        }

        //Invalid handler
        throw new InvalidArgumentException('The http client handler must be an instance of GuzzleHttp\Client or an instance of xcoobee\http\clients\HttpClientInterface.');
    }
}
