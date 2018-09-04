<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class InboxTest extends IntegrationTestCase
{
    public function testListInboxApi()
    {
        $inboxData = self::$xcoobee->inbox->listInbox();
        $this->assertEquals(200, $inboxData->code);
    }
    
    public function testGetInboxItem()
    {
        $inboxData = self::$xcoobee->inbox->listInbox();
        $messageId = $inboxData->result->inbox->data[0]->messageId;
        $inboxItem = self::$xcoobee->inbox->getInboxItem($messageId);
        
        $this->assertEquals(200, $inboxItem->code);
    }
    
    public function testDeleteInboxItem()
    {
        $inboxData = self::$xcoobee->inbox->listInbox();
        $messageId = $inboxData->result->inbox->data[0]->messageId;
        $deleteData = self::$xcoobee->inbox->deleteInboxItem($messageId);
        
        $this->assertEquals(200, $deleteData->code);
    }

}
