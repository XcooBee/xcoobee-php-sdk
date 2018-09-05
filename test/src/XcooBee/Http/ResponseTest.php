<?php

namespace Test\XcooBee\Http;

use XcooBee\Test\TestCase;

class Response extends TestCase {

    /**
     * @param $returnedCode
     * @param $returnedData
     * @param $returnedErrors
     * @param $expectedCode
     * @param $request_id
     * 
     * @dataProvider responseProvider
     */
    public function testGetNextPage($returnedCode, $returnedData, $returnedErrors, $expectedCode, $request_id)
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
        $responseMock = $this->_getMock(\XcooBee\Http\Response::class, [
            '_getNextPagePointer' => 'testEndCursor'
        ]);
        $responseMock->result = true;
        
        $this->_setProperty($responseMock, 'request', $requestMock);
        $response = $responseMock->getNextPage();
        $this->assertEquals($expectedCode, $response->code);
        $this->assertEquals($returnedData, $response->result);
        $this->assertEquals($returnedErrors, $response->errors);
        $this->assertEquals($request_id, $response->request_id);
        $this->assertEquals(true, $response->getPreviousPage()->result);
    }

    public function testHasNextPage()
    {
        $responseMock = $this->_getMock(\XcooBee\Http\Response::class, [
            '_getNextPagePointer' => 'testEndCursor'
        ]);
        $hasNextPage = $responseMock->hasNextPage();
        $this->assertEquals(true, $hasNextPage);
    }

    public function testHasNextPage_False()
    {
        $responseMock = $this->_getMock(\XcooBee\Http\Response::class, [
            '_getNextPagePointer' => null
        ]);
        $hasNextPage = $responseMock->hasNextPage();
        $this->assertEquals(false, $hasNextPage);
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
