<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use \XcooBee\Core\Api\Users as User;

class UsersTest extends TestCase {

    public function testGetConversation()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getPageSize' => true
        ]);
        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['first' => true, 'after' => null, 'userId' => 'testuserId'], $params);
                        }));

        $usersMock->getConversation('testuserId');
    }

    public function testGetConversation_UseConfig()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getPageSize' => true
        ]);
        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params, $config) {
                            $this->assertEquals(['first' => true, 'after' => null, 'userId' => 'testuserId'], $params);
                            $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
                        }));

        $usersMock->getConversation('testuserId', [
            'apiKey' => 'testapikey' ,
            'apiSecret' => 'testapisecret'
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

    public function testGetConversations()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserId' => 'testUserID',
            '_getPageSize' => true
        ]);
        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['userId' => 'testUserID', 'first' => true, 'after' => null], $params);
        }));

        $usersMock->getConversations();
    }

    public function testGetConversations_UseConfig()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserId' => 'testUserID',
            '_getPageSize' => true
        ]);

        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
        }));

        $usersMock->getConversations([
            'apiKey' => 'testapikey' ,
            'apiSecret' => 'testapisecret'
        ]);
    }

    public function testGetUserPublicKey()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse(
                200,
                (object) [
                    'users' => (object) [
                        'data' => [
                            (object) [
                                'pgp_public_key' => 'pgp public key'
                            ]
                        ]
                    ]
                ]
            ),
        ]);

        $this->assertEquals('pgp public key', $usersMock->getUserPublicKey('~test'));
    }

    public function testGetUserPublicKey_UseConfig()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse(
                200,
                (object) [
                    'users' => (object) [
                        'data' => [
                            (object) [
                                'pgp_public_key' => 'pgp public key'
                            ]
                        ]
                    ]
                ]
            ),
        ]);

        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['xid' => '~test'], $params);
                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $this->assertEquals('pgp public key', $usersMock->getUserPublicKey('~test', [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret'
        ]));
    }

    public function testGetUserPublicKey_NoPgpFound()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => $this->_createResponse(
                200,
                (object) [
                    'users' => (object) [
                        'data' => []
                    ]
                ]
            ),
        ]);

        $this->assertNull($usersMock->getUserPublicKey('~test'));
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testGetUserPublicKey_NoXid()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, []);

        $usersMock->getUserPublicKey(null);
    }

    public function testSendUserMessage()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByReference' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => [
                        'message' => 'test message',
                        'reference_cursor' => 'testconsentId',
                        'note_type' => 'consent',
                        'user_cursor' => 'testuserID',
                    ]], $params);
            }));

        $usersMock->sendUserMessage('test message', ['consentId' => 'testconsentId']);
    }

    public function testSendUserMessage_UseTicketId()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByReference' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => [
                        'message' => 'test message',
                        'reference_cursor' => 'testTicketId',
                        'note_type' => 'ticket',
                        'user_cursor' => 'testuserID',
                    ]], $params);
            }));

        $usersMock->sendUserMessage('test message', ['ticketId' => 'testTicketId']);
    }

    public function testSendUserMessage_UseDataRequestRef()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByReference' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params) {
                $this->assertEquals(['config' => [
                        'message' => 'test message',
                        'reference_cursor' => 'dataRequestRef',
                        'note_type' => 'data_request',
                        'user_cursor' => 'testuserID',
                    ]], $params);
            }));

        $usersMock->sendUserMessage('test message', ['requestRef' => 'dataRequestRef']);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testSendUserMessage_InvalidType()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, []);

        $usersMock->sendUserMessage('test message', ['test' => 'dataRequestRef']);
    }

    public function testSendUserMessage_UseConfig()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByReference' => 'testuserID'
        ]);

        $usersMock->expects($this->once())
            ->method('_request')
            ->will($this->returnCallback(function ($query, $params, $config) {
                $this->assertEquals(['config' => [
                    'message' => 'test message',
                    'reference_cursor' => 'testconsentId',
                    'note_type' => 'consent',
                    'user_cursor' => 'testuserID',
                ]], $params);

                $this->assertEquals(['apiKey' => 'testapikey', 'apiSecret' => 'testapisecret'], $config);
            }));

        $usersMock->sendUserMessage('test message', ['consentId' => 'testconsentId'], [
            'apiKey' => 'testapikey',
            'apiSecret' => 'testapisecret',
        ]);
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testSendUserMessageInvalidConsent()
    {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_getUserIdByReference' => false
        ]);

        $usersMock->sendUserMessage('test message', ['consentId' => 'testconsentId']);
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
