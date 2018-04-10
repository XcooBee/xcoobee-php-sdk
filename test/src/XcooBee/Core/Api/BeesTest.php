<?php

namespace Test\XcooBee\Core\Api;


use XcooBee\Core\Api\Users;
use XcooBee\Http\FileUploader;
use XcooBee\Test\TestCase;

class Bees extends TestCase
{
    public function testUploadFiles_Upload2Files()
    {
        $bessMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test',
            '_getPolicy' => (object) [
                'data' => (object) [
                    'policy0' => 'test',
                    'policy1' => 'test',
                ]
            ]
        ]);

        $this->_setProperty($bessMock, '_users', $this->_getMock(Users::class, [
            'getUser' => (object) ['userCursor' => 'test']
        ]));
        $this->_setProperty($bessMock, '_fileUploader', $this->_getMock(FileUploader::class, [
            'uploadFile' => true,
        ]));

        $result = $bessMock->uploadFiles(['1.txt', '2.txt']);

        $this->assertEquals(2, count($result));
        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
    }
}