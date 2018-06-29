<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;

class InboxTest extends TestCase 
{

    /**
     * @param array $inboxItems
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * 
     * @dataProvider inboxItemsProvider
     */
    public function testListInbox($requestCode, $requestData, $requestError)
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError)
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['after' => null], $params);
                        }));

        $inboxMock->listInbox();
    }

    /**
     * @param array $inboxItems
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * 
     * @dataProvider inboxItemsProvider
     */
    public function testListInbox_withStartId($requestCode, $requestData, $requestError)
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError)
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['after' => '2015-08-09T11:39:31Z'], $params);
                        }));

        $inboxMock->listInbox('2015-08-09T11:39:31Z');
    }

    /**
     * @param array $inboxItems
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * 
     * @dataProvider inboxItemProvider
     */
    public function testGetInboxItem($requestCode, $requestData, $requestError)
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => 'testUserId'
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['userId' => "testUserId", 'filename' => 'testFileName'], $params);
                        }));
        $inboxMock->getInboxItem('testFileName');
    }

    public function testDeleteInboxItem()
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => true,
            '_getUserId' => "testUserId"
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['userId' => "testUserId", 'filename' => 'testFileName'], $params);
                        }));
        $inboxMock->deleteInboxItem('testFileName');
    }

    public function inboxItemProvider()
    {
        return [
            [
                200,
                (object) [
                    'inbox_item' => (object) [
                        'download_link' => 'testDownloadLink',
                        'info' => [
                            'file_type' => 'testOriginalName',
                            'file_tags' => 'testFileName',
                            'user_ref' => 'testFileSize',
                        ]
                    ]
                ],
                []
            ],
            [
                400,
                (object) [
                    'inbox_item' => []
                ],
                ['testErrorMessage']
            ]
        ];
    }

    public function inboxItemsProvider() 
    {
        return [
            [
                200,
                (object) [
                    'inbox' => (object) [
                        'data' => [(object) [
                                'original_name' => 'testOriginalName',
                                'filename' => 'testFileName',
                                'file_size' => 'testFileSize',
                                'sender' => ['from' => 'testFromId', 'from_xcoobee_id' => 'testXcooBeeId'],
                                'date' => '2018-06-01T07:12:42Z',
                                'downloaded' => 'testDownloaddate'
                            ],
                            (object) [
                                'original_name' => 'testOriginalName',
                                'filename' => 'testFileName',
                                'file_size' => 'testFileSize',
                                'sender' => ['from' => 'testFromId', 'from_xcoobee_id' => 'testXcooBeeId'],
                                'date' => '2018-06-01T07:12:42Z',
                                'downloaded' => 'testDownloaddate'
                            ],
                            (object) [
                                'original_name' => 'testOriginalName',
                                'filename' => 'testFileName',
                                'file_size' => 'testFileSize',
                                'sender' => ['from' => 'testFromId', 'from_xcoobee_id' => 'testXcooBeeId'],
                                'date' => '2018-06-01T07:12:42Z',
                                'downloaded' => 'testDownloaddate'
                            ],
                        ]
                    ]
                ],
                []
            ],
            [
                400,
                (object) [
                    'inbox' => (object) [
                        'data' => []
                    ]
                ],
                ['testError']
            ]
        ];
    }

}
