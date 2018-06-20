<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;

class InboxTest extends TestCase
{

    public function testListInbox()
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => true,
        ]);
        
        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['after' => null], $params);
                        }));

        $inboxMock->listInbox();
    }

    public function testListInbox_withStartId()
    {
        $inboxMock = $this->_getMock(\XcooBee\Core\Api\Inbox::class, [
            '_request' => true,
        ]);

        $inboxMock->expects($this->once())
                ->method('_request')
                ->will($this->returnCallback(function ($query, $params) {
                            $this->assertEquals(['after' => '2015-08-09T11:39:31Z'], $params);
                        }));

        $inboxMock->listInbox('2015-08-09T11:39:31Z');
    }

    public function testGetInboxItem()
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
}
