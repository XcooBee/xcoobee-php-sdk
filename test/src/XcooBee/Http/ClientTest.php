<?php

namespace Test\XcooBee\Http;


use XcooBee\Test\TestCase;

class Client extends TestCase
{
    public function testPost()
    {
        $clientMock = $this->_getMock(\XcooBee\Http\Client::class, []);
        $httpClientMock = $this->_getMock(\GuzzleHttp\Client::class, ['request' => null]);
        $httpClientMock->expects($this->once())
            ->method('request')
            ->will($this->returnCallback(function ($method, $uri, $data) {
                $this->assertEquals('POST', $method);
                $this->assertEquals('test', $uri);
                $this->assertEquals(['data'], $data);
            }));
        $this->_setProperty($clientMock, '_client', $httpClientMock);

        $clientMock->post('test', ['data']);
    }
}