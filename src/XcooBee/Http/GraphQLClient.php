<?php

namespace XcooBee\Http;

use XcooBee\Http\Request;
use XcooBee\Http\Response;

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
        $request = new Request($this->_getUriFromEndpoint(self::API_URL));
        $request->setHeaders($headers);
        $request->setVariables($variables);
        $request->setQuery($query);
        $responsedata = Response::setFromHttpResponse($request->makeCall());
        $responsedata->request = $request;

        return $responsedata;
    }
}
