<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class InboxTest extends IntegrationTestCase 
{

    public function testListInbox()
    {
        $inboxData = $this->_xcoobee->inbox->listInbox();
        $this->assertEquals(200, $inboxData->code);
    }

    public function testGetInboxItem()
    {
        $this->_xcoobee->inbox->getInboxItem("mytestmessge.jpg.eee03714-11c6-4134-af22-0d71338d12ce");
    }

    public function testDeleteInboxItem()
    {
        
    }

}
