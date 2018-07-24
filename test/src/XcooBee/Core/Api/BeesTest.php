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
                'data' => (object) [
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

        $result = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/test.txt', __DIR__ . '/../../../../assets/test2.txt']);

        $this->assertEquals(2, count($result));
        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
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
                'data' => (object) [
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
                'data' => (object) [
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

        $result = $beesMock->uploadFiles([__DIR__ . '/../../../../assets/test.txt', __DIR__ . '/../../../../assets/test2.txt'], [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);

        $this->assertEquals(2, count($result));
        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
    }
    
    public function testListBees()
    {
        
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_request' => $this->_createResponse(200, (object)[
                'bees' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ])
        ]);
        
        $consentsMock->listBees();
    }
    
    public function testListBees_UseConfig()
    {
        $consentsMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_request' => $this->_createResponse(200, (object)[
                'bees' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ])
        ]);
        $consentsMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
        }));
        
        $consentsMock->listBees(null,['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret']);
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
