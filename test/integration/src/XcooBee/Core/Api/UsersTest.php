<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class UsersTest extends IntegrationTestCase
{

    public function testGetUser()
    {
        $user = $this->_xcoobee->users->getUser();
        $this->assertEquals('AvPfoQD54o6bJ5t2CblzenSrwp8COIZL/JJxAjNGCGK1EKV+1lxtybvnQE9iJqLmVyRQKg==', $user->userId);
        $this->assertEquals('~ganesh_', $user->xcoobeeId);
    }

    public function testGetConversations()
    {
        $response = $this->_xcoobee->users->getConversations();
        $this->assertEquals('200', $response->code);
        $this->assertEquals((object)[
            'display_name' => 'Volodymyr Rabeshko',
            'consent_cursor' => 'AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==',
            'target_cursor' => 'AvPfoQD5u4CYIJpyC7lzKHKhzp8CaIUa/JJ/VTBGWmO1QPkq1ARsy7uwQE9iJqLmVyRQKg==',
            'date_c' => '2018-07-06T12:41:57Z'
        ], $response->data->conversations->data[0]);
    }
    
    public function testSendUserMessage()
    {
        $response = $this->_xcoobee->users->sendUserMessage("test message", 'AvPfoQD56I3NJ8h+CulzdHT2z58COtEd/JMjUDZGXm7pQKIo2gdqzL24QE9iJqLmVzJQKg==');
        $this->assertEquals('200', $response->code);
        $this->assertEquals((object)['note_text' => "test message"], $response->data->send_message);
    }
    
    public function testGetConversation()
    {
        $response = $this->_xcoobee->users->getConversation('AvPfoQD54o6bJ5t2CblzenSrwp8COIZL/JJxAjNGCGK1EKV+1lxtybvnQE9iJqLmVyRQKg==');
        $this->assertEquals('200', $response->code);
    }
    
}
