<?php

namespace Test\XcooBee\Http;

use XcooBee\Test\TestCase;

class Request extends TestCase {

    public function testMakeCall()
    {
        $requestMock = $this->_getMock(\XcooBee\Http\Request::class, []);
        $requestMock->setQuery('testQuery');
        $requestMock->setVariables(['test' => 'testVariable']);
        $requestMock->setHeaders(['testHeader' => 'testHeaderValue']);
        
        $this->_setProperty($requestMock, '_uri', 'testUri');

        $clientMock = $this->_getMock(\XcooBee\Http\Client::class, [
            'post' => true,
        ]);
        $clientMock->expects($this->once())
                ->method('post')
                ->will($this->returnCallback(function ($uri, $data) {
                        $this->assertEquals('testUri', $uri);
                        $this->assertEquals([
                            'json' => [
                                'query' => 'testQuery',
                                'variables' => ['test' => 'testVariable']
                            ],
                            'headers' => ['testHeader' => 'testHeaderValue']
                        ], $data);
                }));

        $this->_setProperty($requestMock, '_client', $clientMock);

        $requestMock->makeCall();
    }

}
