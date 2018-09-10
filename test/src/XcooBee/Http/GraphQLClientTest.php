<?php

namespace Test\XcooBee\Http;


use XcooBee\Test\TestCase;

class GraphQLClient extends TestCase
{

    /**
     * @param $returnedCode
     * @param $returnedData
     * @param $returnedErrors
     * @param $expectedCode
     * @param $request_id
     * 
     * @dataProvider responseProvider
     */
    public function testRequest($returnedCode, $returnedData, $returnedErrors, $expectedCode, $request_id)
    {
        $guzzleResponseMock = $this->_getMock(\GuzzleHttp\Psr7\Response::class, [
            'getBody' => json_encode([
                'data' => $returnedData,
                'errors' => $returnedErrors,
                'request_id' => $request_id
            ]),
            'getStatusCode' => $returnedCode,
        ]);
        $requestMock = $this->_getMock(\XcooBee\Http\Request::class, [
            'makeCall' => $guzzleResponseMock
        ]);
        $graphQLClientMock = $this->_getMock(\XcooBee\Http\GraphQLClient::class, [
            '_getAuthToken' => 'token',
            '_getUriFromEndpoint' => null,
            '_getRequest' => $requestMock,
        ]);

        $response = $graphQLClientMock->request('query');

        $this->assertEquals($expectedCode, $response->code);
        $this->assertEquals($returnedData, $response->result);
        $this->assertEquals($returnedErrors, $response->errors);
        $this->assertEquals($request_id, $response->request_id);
    }

    public function responseProvider()
    {
        return [
            [
                200,
                'data',
                [],
                200,
                'testRequestId'
            ],
            [
                200,
                'data',
                (object) ['message' => 'invalid data'],
                400,
                'testRequestId'
            ],
            [
                404,
                null,
                (object) ['message' => 'not found'],
                404,
                'testRequestId'
            ],
            [
                400,
                null,
                (object) ['message' => 'invalid data'],
                400,
                'testRequestId'
            ]
        ];
    }
}
