<?php

namespace XcooBee\Http;

class GraphQLClient extends Client
{
    const API_URL = 'graphql';

    /**
     * Make a GraphQL Request and get the guzzle response .
     *
     * @param $query string
     * @param $variables array
     * @param $headers array
     * @param array $config
     * @return Response
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($query, $variables = [], $headers = [], $config = [])
    {
        $headers["Authorization"] = $this->_getAuthToken($config);

        return Response::setFromHttpResponse($this->post($this->_getUriFromEndpoint(self::API_URL), [
            'json' => [
                'query' => $query,
                'variables' => $variables
            ],
            'headers' => $headers
        ]));
    }
}
