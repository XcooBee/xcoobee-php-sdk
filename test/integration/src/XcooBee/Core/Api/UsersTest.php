<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class UsersTest extends IntegrationTestCase
{
    public function testGetUser()
    {
        $user = $this->_xcoobee->users->getUser();
        $this->_userId = $user->userId;
        $this->assertEquals('~demo_user', $user->xcoobeeId);
    }

    public function testGetConversations()
    {
        $response = $this->_xcoobee->users->getConversations();
        $this->assertEquals('200', $response->code);
        $this->assertEquals('Ganesh Test', $response->result->conversations->data[0]->display_name);
    }

    public function testSendUserMessage()
    {
        $response = $this->_xcoobee->users->sendUserMessage("test message", $this->_consentId);
        $this->assertEquals('200', $response->code);
        $this->assertEquals((object) ['note_text' => "test message"], $response->result->send_message);
    }

    public function testGetConversation()
    {
        $user = $this->_xcoobee->users->getUser();
        $response = $this->_xcoobee->users->getConversation($user->userId);
        $this->assertEquals('200', $response->code);
    }

}
