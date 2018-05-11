<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;
use \XcooBee\Core\Api\Users as User;

class Users extends TestCase {

    public function testGetConversation() {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
        ]);
        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['userId' => 'testuserId', 'first' => null, 'after' => null], $params);
                        }));

        $usersMock->getConversation('testuserId');
    }

    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testgetConversation_noConversationID() {
        $users = new User();

        $users->getConversation(null);
    }

    public function testGetConversations() {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
        ]);

        $usersMock->getConversations();
    }
    
    public function testsendUserMessage() {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent'=>'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                'message' => 'test message', 
                                'consent_cursor' => 'testconsentId', 
                                'note_type' => 'consent',
                                'user_cursor'=> 'testuserID',
                                'breach_cursor'=>null
                            ]], $params);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId' );
    }
    
    public function testsendUserMessage_withbreachId() {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent'=>'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                'message' => 'test message', 
                                'consent_cursor' => 'testconsentId', 
                                'note_type' => 'breach',
                                'user_cursor'=> 'testuserID', 
                                'breach_cursor'=>'testBreachID'
                            ]], $params);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId', 'testBreachID' );
    }
    
    public function testsendUserMessage_breachIdNotprovided() {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_request' => true,
            '_getUserIdByConsent'=>'testuserID'
        ]);

        $usersMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['config' => [
                                'message' => 'test message', 
                                'consent_cursor' => 'testconsentId', 
                                'note_type' => 'consent',
                                'user_cursor'=> 'testuserID',
                                'breach_cursor'=>null
                            ]], $params);
                        }));

        $usersMock->sendUserMessage('test message', 'testconsentId', null );
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testsendUserMessage_invalidconsent() {
        $usersMock = $this->_getMock(\XcooBee\Core\Api\Users::class, [
            '_getUserIdByConsent'=>false
        ]);

        $usersMock->sendUserMessage('test message', 'testconsentId', null );
    }
    
    /**
     * @expectedException \XcooBee\Exception\XcooBeeException
     */
    public function testsendUserMessage_nodataProvided() {
        $users = new User();

        $users->getConversation(null, null, null);
    }

}
