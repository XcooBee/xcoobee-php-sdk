<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Core\Api\Users;
use XcooBee\Http\FileUploader;
use XcooBee\Test\TestCase;

class BeesTest extends TestCase
{
    public function testUploadFiles_Upload2Files()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['userId' => 'test']
        ]);
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test',
            '_getPolicy' => (object) [
                'result' => (object) [
                    'policy0' => 'test',
                    'policy1' => 'test',
                ],
                'errors' => []
            ]
        ]);
        $this->_setProperty($beesMock, '_xcoobee', $XcooBeeMock);
        $this->_setProperty($beesMock, '_fileUploader', $this->_getMock(FileUploader::class, [
            'uploadFile' => true,
        ]));

        $response = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/test.txt', __DIR__ . '/../../../../assets/test2.txt']);
        
        $this->assertTrue($response->result);
        $this->assertEquals(200, $response->code);
    }
    
    public function testUploadFiles_UploadFiles_Error()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['userId' => 'test']
        ]);
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test',
            'uploadFiles' => $this->_createResponse(400, false, [(object) ['message' => 'testErrorMessage']]),
            '_getPolicy' => (object) [
                'result' => (object) [
                    'policy0' => 'test',
                ],
                'errors' => []
            ]
        ]);
        $this->_setProperty($beesMock, '_xcoobee', $XcooBeeMock);
        $this->_setProperty($beesMock, '_fileUploader', $this->_getMock(FileUploader::class, [
            'uploadFile' => true,
        ]));

        $response = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/test.txt']);
 
        $this->assertEquals(false, $response->result);
        $this->assertEquals(400, $response->code);
        $this->assertEquals('testErrorMessage', $response->errors[0]->message);
    }
    
    public function testUploadFiles_InvalidExtension()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['userId' => 'test']
        ]);
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test',
            '_getPolicy' => (object) [
                'result' => (object) [
                    'policy0' => [],
                    'policy1' => [],
                ],
                'errors' => [
                    (object) [
                        'message' => 'rar files are not allowed',
                    ],
                    (object) [
                        'message' => 'sql files are not allowed',
                    ]
                ]
            ]
        ]);
        $this->_setProperty($beesMock, '_xcoobee', $XcooBeeMock);
        $this->_setProperty($beesMock, '_fileUploader', $this->_getMock(FileUploader::class, [
            'uploadFile' => true,
        ]));

        $result = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/test.rar', __DIR__ . '/../../../../assets/test.sql']);
        
        $this->assertEquals(400, $result->code);
        $this->assertEquals('rar files are not allowed', $result->errors[0]->message);
        $this->assertEquals('sql files are not allowed', $result->errors[1]->message);
    }
    
    public function testUploadFiles_InvalidFile()
    {
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test'
        ]);

        $result = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/invalidfile.rar', __DIR__ . '/../../../../assets/xyz.sql']);

        $this->assertEquals('Invalid File', $result->errors[0]->message);
        $this->assertEquals('Invalid File', $result->errors[1]->message);
    }
    
    public function testUploadFiles_Upload2Files_UseConfig()
    {
        $XcooBeeMock = $this->_getMock(XcooBee::class, [] );
        $XcooBeeMock->users = $this->_getMock(Users::class, [
            'getUser' => (object) ['userId' => 'test']
        ]);
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test',
            '_getPolicy' => (object) [
                'result' => (object) [
                    'policy0' => 'test',
                    'policy1' => 'test',
                ],
                'errors' => []
            ]
        ]);
        $this->_setProperty($beesMock, '_xcoobee', $XcooBeeMock);
        $this->_setProperty($beesMock, '_fileUploader', $this->_getMock(FileUploader::class, [
            'uploadFile' => true,
        ]));

        $response = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/test.txt', __DIR__ . '/../../../../assets/test2.txt'], [
            'apiKey' => 'testapikey' , 
            'apiSecret' => 'testapisecret' 
        ]);

        $this->assertTrue($response->result);
        $this->assertEquals(200, $response->code);
    }
    
    public function testListBees()
    {
        
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_request' => true,
            '_getPageSize' => true,
        ]);
        $beesMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['searchText' => null, 'first' => true, 'after' => null], $params);
        }));
        
        $beesMock->listBees();
    }
    
    public function testListBees_withSearch()
    {
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_request' => true,
            '_getPageSize' => true,
        ]);
        $beesMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['searchText' => 'testSearchText', 'first' => true, 'after' => null], $params);
        }));
        
        $beesMock->listBees('testSearchText');
    }
    
    public function testListBees_UseConfig()
    {
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_request' => true,
            '_getPageSize' => true,
        ]);
        $beesMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['searchText' => null, 'first' => true, 'after' => null], $params);
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
        }));
        
        $beesMock->listBees(null, ['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret']);
    }
    
    /**
     * @param array $bees
     * @param array $params
     * @param array $subscriptions
     * @param array $paramsExpected
     *
     * @dataProvider takeOffProvider
     */
    public function testTakeOff($bees, $params, $subscriptions, $paramsExpected)
    {
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_request' => true,
        ]);

        $beesMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) use ($paramsExpected) {
                $this->assertEquals($paramsExpected, $params);
            }));

        $beesMock->takeOff($bees, $params, $subscriptions);
    }

    public function takeOffProvider() 
    {
        return [
            [
                [
                    'xcoobee_message' => [
                        'xcoobee_simple_message' => ['message' => 'test' ],
                        'recipient' => [ 'xcoobee_id' => '~test' ],
                    ],
                ],
                [
                    'process' => [
                        'destinations' => [ '~test' ],
                    ]
                ],
                [],
                [
                    'params' => [
                        'user_reference' => null,
                        'destinations' => [
                            ['xcoobee_id' => '~test'],
                        ],
                        'bees' => [
                            [
                                'bee_name' => 'xcoobee_message',
                                'params' => '{"xcoobee_simple_message":{"message":"test"},"recipient":{"xcoobee_id":"~test"}}',
                            ]
                        ],
                    ]
                ],
            ],
            [
                [
                    'transfer' => [
                        'message' => 'test',
                    ],
                ],
                [
                    'process' => [
                        'fileNames' => ['1.jpg', '2.jpg'],
                        "destinations" => ['~test']
                    ]
                ],
                [],
                [
                    'params' => [
                        'filenames' => ['1.jpg', '2.jpg'],
                        'user_reference' => null,
                        'destinations' => [
                            ['xcoobee_id' => '~test'],
                        ],
                        'bees' => [],
                    ]
                ],
            ],
            [
                [
                    'transfer' => [],
                ],
                [
                    'process' => [
                        'fileNames' => ['1.jpg', '2.jpg'],
                        "destinations" => ['~test', 'test@xcoobee.com']
                    ]
                ],
                [],
                [
                    'params' => [
                        'filenames' => ['1.jpg', '2.jpg'],
                        'user_reference' => null,
                        'destinations' => [
                            ['xcoobee_id' => '~test'],
                            ['email' => 'test@xcoobee.com'],
                        ],
                        'bees' => [],
                    ]
                ],
            ],
            [
                [
                    'xcoobee_twitter_base' => [
                        'message' => 'test',
                    ],
                ],
                [
                    'process' => [
                        'fileNames' => ['1.jpg', '2.jpg'],
                    ]
                ],
                [],
                [
                    'params' => [
                        'filenames' => ['1.jpg', '2.jpg'],
                        'user_reference' => null,
                        'bees' => [
                            [
                                'bee_name' => 'xcoobee_twitter_base',
                                'params' => '{"message":"test"}',
                            ]
                        ],
                    ]
                ],
            ],
            [
                [
                    'xcoobee_twitter_base' => [
                        'message' => 'test',
                    ],
                ],
                [
                    'process' => [
                        'fileNames' => ['1.jpg', '2.jpg'],
                    ]
                ],
                [
                    [
                        'target' => 'https://testapi.xcoobee.net/Test',
                        'signed' => false,
                        'events' => 'success,error',
                    ]
                ],
                [
                    'params' => [
                        'filenames' => ['1.jpg', '2.jpg'],
                        'user_reference' => null,
                        'subscriptions' => [
                            [
                                'target' => 'https://testapi.xcoobee.net/Test',
                                'signed' => false,
                                'events' => 'success,error',
                            ]
                        ],
                        'bees' => [
                            [
                                'bee_name' => 'xcoobee_twitter_base',
                                'params' => '{"message":"test"}',
                            ]
                        ],
                    ]
                ],
            ],
            [
                [
                    'xcoobee_twitter_base' => [],
                ],
                [
                    'process' => [
                        'fileNames' => ['1.jpg', '2.jpg'],
                    ]
                ],
                [],
                [
                    'params' => [
                        'filenames' => ['1.jpg', '2.jpg'],
                        'user_reference' => null,
                        'bees' => [
                            [
                                'bee_name' => 'xcoobee_twitter_base',
                                'params' => '{}',
                            ]
                        ],
                    ]
                ],
            ],
        ];
    }
}
