<?php

namespace Test\XcooBee\Http;


use XcooBee\Test\TestCase;

class GraphQLClient extends TestCase
{

    /**
     * @param $returnedCode
     * @param $returnedData
     * @param $returnedErrors
     *
     * @dataProvider responseProvider
     */
    public function testRequest($returnedCode, $returnedData, $returnedErrors)
    {
        $guzzleResponseMock = $this->_getMock(\GuzzleHttp\Psr7\Response::class, [
            'getBody' => json_encode([
                'data' => $returnedData,
                'errors' => $returnedErrors,
            ]),
            'getStatusCode' => $returnedCode,
        ]);
        $graphQLClientMock = $this->_getMock(\XcooBee\Http\GraphQLClient::class, [
            '_getAuthToken' => 'token',
            '_getUriFromEndpoint' => null,
            'post' => $guzzleResponseMock,
        ]);

        $response = $graphQLClientMock->request('query');

        $this->assertEquals($returnedCode, $response->code);
        $this->assertEquals($returnedData, $response->data);
        $this->assertEquals($returnedErrors, $response->errors);
    }

    public function responseProvider()
    {
        return [
            [
                200,
                'data',
                (object) [],
            ],
            [
                404,
                null,
                (object) ['message' => 'not found']
            ]
        ];
    }
}