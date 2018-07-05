<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class InboxTest extends IntegrationTestCase 
{

    public function testListInbox()
    {
        $inboxData = $this->_xcoobee->inbox->listInbox();
        print_r($inboxData);
        //$this->assertEquals(200, $inboxData->code);
    }

    public function testGetInboxItem()
    {
        $response = $this->_xcoobee->inbox->getInboxItem("mytestmessge.jpg.eee03714-11c6-4134-af22-0d71338d12ce");
        print_r($response);
    }

    public function testDeleteInboxItem()
    {
        
    }

}
