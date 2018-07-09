<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class InboxTest extends IntegrationTestCase
{

    public function testInboxApi()
    {
        $inboxApi = $this->_xcoobee->inbox;
        $inboxData = $inboxApi->listInbox();
        if (isset($inboxData->data->inbox->data[0])) {
            echo $inboxData->data->inbox->data[0]->messageId;
            $inboxItem = $inboxApi->getInboxItem($inboxData->data->inbox->data[0]->messageId);
            $deleteData = $inboxApi->deleteInboxItem($inboxData->data->inbox->data[0]->messageId);
            $this->assertEquals(200, $inboxItem->code);
            $this->assertEquals(200, $deleteData->code);
        }
        $this->assertEquals(200, $inboxData->code);
    }

}
