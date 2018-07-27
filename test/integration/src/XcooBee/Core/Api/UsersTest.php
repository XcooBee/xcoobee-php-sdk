<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class UsersTest extends IntegrationTestCase
{
    public function testGetUser()
    {
        $user = $this->_xcoobee->users->getUser();
        $this->_userId = $user->userId;
        $this->assertEquals('~ganesh_', $user->xcoobeeId);
    }

    public function testGetConversations()
    {
        $response = $this->_xcoobee->users->getConversations();
        $keys = array_keys($response->data->conversations);
        $this->assertEquals('200', $response->code);
        $this->assertEquals('test test122', $response->data->conversations[end($keys)]->display_name);
        $this->assertEquals(null, $response->data->conversations[end($keys)]->consent_cursor);
        $this->assertEquals('2018-05-09T09:33:53Z', $response->data->conversations[end($keys)]->date_c);
    }

    public function testSendUserMessage()
    {
        $response = $this->_xcoobee->users->sendUserMessage("test message", $this->_consentId);
        $this->assertEquals('200', $response->code);
        $this->assertEquals((object) ['note_text' => "test message"], $response->data->send_message);
    }

    public function testGetConversation()
    {
        $user = $this->_xcoobee->users->getUser();
        $response = $this->_xcoobee->users->getConversation($user->userId);
        $this->assertEquals('200', $response->code);
    }

}
