<?php

namespace Test\XcooBee\Core\Api;


use XcooBee\Core\Api\Users;
use XcooBee\Http\FileUploader;
use XcooBee\Test\TestCase;

class Bees extends TestCase
{
    public function testUploadFiles_Upload2Files()
    {
        $beesMock = $this->_getMock(\XcooBee\Core\Api\Bees::class, [
            '_getOutboxEndpoint' => 'test',
            '_getPolicy' => (object) [
                'data' => (object) [
                    'policy0' => 'test',
                    'policy1' => 'test',
                ]
            ]
        ]);

        $this->_setProperty($beesMock, '_users', $this->_getMock(Users::class, [
            'getUser' => (object) ['userId' => 'test']
        ]));
        $this->_setProperty($beesMock, '_fileUploader', $this->_getMock(FileUploader::class, [
            'uploadFile' => true,
        ]));

        $result = $beesMock->uploadFiles(['1.txt', '2.txt']);

        $this->assertEquals(2, count($result));
        $this->assertTrue($result[0]);
        $this->assertTrue($result[1]);
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

    public function takeOffProvider() {
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
