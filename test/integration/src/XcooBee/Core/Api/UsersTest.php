<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class UsersTest extends IntegrationTestCase
{
    public function testGetUser()
    {
        $user = self::$xcoobee->users->getUser();
        $this->assertInstanceOf('XcooBee\Models\UserModel', $user);
    }

    public function testGetConversations()
    {
        $response = self::$xcoobee->users->getConversations();
        $this->assertEquals('200', $response->code);
    }

    public function testGetConversation()
    {
        $user = self::$xcoobee->users->getUser();
        $response = self::$xcoobee->users->getConversation($user->userId);
        $this->assertEquals('200', $response->code);
    }
}
