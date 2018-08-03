<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class BeesTest extends IntegrationTestCase
{

    public function testListBees()
    {
        $bees = $this->_xcoobee->bees->listBees();
        $this->assertEquals(200, $bees->code);
        $this->assertEquals('xcoobee_dropbox_uploader', $bees->result->bees->data[0]->bee_system_name);
        $this->assertEquals('transport', $bees->result->bees->data[0]->category);
        $this->assertEquals('xcoobee_dropbox_uploader', $bees->result->bees->data[0]->bee_icon);
        $this->assertEquals('DropBox Upload Bee', $bees->result->bees->data[0]->label);
        $this->assertEquals('With the Dropbox Upload Bee you can securely upload any file to your Dropbox account.', $bees->result->bees->data[0]->description);
        $this->assertEquals(true, $bees->result->bees->data[0]->is_file_reader);
    }

    public function testUploadFiles()
    {
        $this->_xcoobee->bees->uploadFiles([__DIR__ . '/../../../../assets/testfile.txt']);
    }

    public function testTakeOff()
    {

        $response = $this->_xcoobee->bees->takeOff(
            [
                'xcoobee_message' => [
                    'xcoobee_simple_message' => ['message' => 'Test post'], 'recipient' => ['xcoobee_id' => '~ganesh_']
                ]
            ], 
            [
                'process' => [
                    'fileNames' => ['testfile.txt'],
                    'destinations' => ["~ganesh_"],
                ],
            ]);
        $this->assertEquals(200, $response->code);
    }

}
