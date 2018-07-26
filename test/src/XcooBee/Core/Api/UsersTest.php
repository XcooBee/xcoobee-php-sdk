<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use \XcooBee\Core\Api\Users as User;

class UsersTest extends TestCase
{
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider conversationProvider
    */
    public function testGetConversation($requestCode, $requestData, $requestError) 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError)
        ]);
        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['userId' => 'testuserId', 'first' => null, 'after' => null], $params);
                        }));

        $response = $usersMock->getConversation('testuserId');
        
        $this->assertEquals($requestCode, $response->code);
	$this->assertEquals($requestData, $response->data);
	$this->assertEquals($requestError, $response->errors);
    }
    
    public function conversationProvider()
    {
        return [
            [
                200,
                (object)[
                    'conversation' => (object)[
                        'data' => (object) [
                            'Field' => 'testFieldValue'
                        ],
                        'page_info' => (object)[
                            'end_cursor' => 'testEndCursor',
                            'has_next_page' => null

                        ]
                    ]
                ],
                []   
            ],
            [
                400,
                (object)[],
                ["message" => 'test error message'],
                []    
            ]
        ];
    }
    
    public function testGetConversation_UseConfig() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse(200, (object)[
                'conversation' => (object)[
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
        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['userId' => 'testuserId', 'first' => null, 'after' => null], $params);
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                        }));

        $usersMock->getConversation('testuserId', null, null, [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testgetConversation_noUserID()
    {
        $xcooBeeMock = $this->_getXcooBeeMock();
        $users = new User($xcooBeeMock);

        $users->getConversation(null);
    }
    
    /**
    * @param int $requestCode
    * @param array $requestData
    * @param array $requestError
    * 
    * @dataProvider conversationsProvider
    */
    public function testGetConversations($requestCode, $requestData, $requestError) 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse($requestCode, $requestData, $requestError),
            '_getUserId' => 'testUserID'
        ]);

        $response = $usersMock->getConversations();
        
        $this->assertEquals($requestCode, $response->code);
	$this->assertEquals($requestData, $response->data);
	$this->assertEquals($requestError, $response->errors);
    }
    
    public function conversationsProvider()
    {
        return [
            [
                200,
                (object)[
                    'conversations' => (object)[
                        'data' => (object) [
                            'Field' => 'testFieldValue'
                        ],
                        'page_info' => (object)[
                            'end_cursor' => 'testEndCursor',
                            'has_next_page' => null

                        ]
                    ]
                ],
                []   
            ],
            [
                400,
                (object)[],
                ["message" => 'test error message'],
                []    
            ]
        ];
    }
    
    public function testGetConversations_UseConfig() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse(200, (object)[
                'conversations' => (object)[
                    'data' => (object) [
                        'Field' => 'testFieldValue'
                    ],
                    'page_info' => (object)[
                        'end_cursor' => 'testEndCursor',
                        'has_next_page' => null
                        
                    ]
                ]
            ]),
            '_getUserId' => 'testUserID'
        ]);
        
        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
        }));
                
        $usersMock->getConversations(null, null, [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);
    }
    
    public function testSendUserMessage() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                    'message' => 'test message',
                                    'consent_cursor' => 'testconsentId',
                                    'note_type' => 'consent',
                                    'user_cursor' => 'testuserID',
                                    'breach_cursor' => null
                                ]], $params);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId');
    }
    
    public function testSendUserMessage_UseConfig() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['config' => [
                                    'message' => 'test message',
                                    'consent_cursor' => 'testconsentId',
                                    'note_type' => 'consent',
                                    'user_cursor' => 'testuserID',
                                    'breach_cursor' => null
                                ]], $params);
                            
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret'=> 'testapisecret'], $config);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId', null, [
            'apiKey'=> 'testapikey' , 
            'apiSecret'=> 'testapisecret' 
        ]);
    }
    
    public function testSendUserMessageWithBreachId() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                    'message' => 'test message',
                                    'consent_cursor' => 'testconsentId',
                                    'note_type' => 'breach',
                                    'user_cursor' => 'testuserID',
                                    'breach_cursor' => 'testBreachID'
                                ]], $params);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId', 'testBreachID');
    }

    public function testSendUserMessageBreachidNotProvided() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                    'message' => 'test message',
                                    'consent_cursor' => 'testconsentId',
                                    'note_type' => 'consent',
                                    'user_cursor' => 'testuserID',
                                    'breach_cursor' => null
                                ]], $params);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId', null);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testSendUserMessageInvalidConsent() 
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_getUserIdByConsent' => false
        ]);

        $usersMock->sendUserMessage('test message', 'testconsentId', null);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testSendUserMessageNodataProvided() 
    {
        $xcooBeeMock = $this->_getXcooBeeMock();
        $users = new User($xcooBeeMock);

        $users->getConversation(null, null, null);
    }

}
