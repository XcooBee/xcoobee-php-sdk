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
     * @param array $expectedResponse
     * 
     * @dataProvider inboxItemsProvider
     */
    public function testListInbox($requestCode, $requestData, $requestError, $expectedResponse)
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError)
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['after' => null], $params);
                        }));

        $response = $inboxMock->listInbox();
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($expectedResponse, $response->data->inbox->data);
    }

    /**
     * @param array $inboxItems
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * @param array $expectedResponse
     * 
     * @dataProvider inboxItemsProvider
     */
    public function testListInbox_withStartId($requestCode, $requestData, $requestError, $expectedResponse)
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError)
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['after' => '2015-08-09T11:39:31Z'], $params);
                        }));

        $response = $inboxMock->listInbox('2015-08-09T11:39:31Z');
        
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($expectedResponse, $response->data->inbox->data);
    }

    /**
     * @param array $inboxItems
     * @param int $requestCode
     * @param array $requestData
     * @param array $requestError
     * @param array $expectedResponse
     * 
     * @dataProvider inboxItemProvider
     */
    public function testGetInboxItem($requestCode, $requestData, $requestError, $expectedResponse)
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
        $response = $inboxMock->getInboxItem('testFileName');
        
        $this->assertEquals($requestCode, $response->code);
        $this->assertEquals($expectedResponse, $response->data->inbox_item);
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
                [],
                (object) [
                    'download_link' => 'testDownloadLink',
                    'info' => [
                        'fileType' => 'testOriginalName',
                        'fileTags' => 'testFileName',
                        'userRef' => 'testFileSize',
                    ]
                ]
            ],
            [
                400,
                (object) [
                    'inbox_item' => []
                ],
                ['testErrorMessage'],
                []
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
                                'downloaded' => '2018-06-01T07:12:42Z'
                            ]
                        ]
                    ]
                ],
                [],
                [(object) [
                    'fileName' => 'testOriginalName',
                    'messageId' => 'testFileName',
                    'fileSize' => 'testFileSize',
                    'sender' => ['from' => 'testFromId', 'from_xcoobee_id' => 'testXcooBeeId'],
                    'receiptDate' => '2018-06-01T07:12:42Z',
                    'expirationDate' => '2018-07-01T07:12:42Z',
                    'downloadDate' => '2018-06-01T07:12:42Z'
                ]] 
            ],
            [
                400,
                (object) [
                    'inbox' => (object) [
                        'data' => []
                    ]
                ],
                ['testError'],
                []
            ]
        ];
    }

}
