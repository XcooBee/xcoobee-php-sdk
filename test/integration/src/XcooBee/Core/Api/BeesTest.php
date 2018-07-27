<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class BeesTest extends IntegrationTestCase
{

    public function testListBees()
    {
        $bees = $this->_xcoobee->bees->listBees();
        $this->assertEquals(200, $bees->code);
        $this->assertEquals('transfer', $bees->data->bees[0]->bee_system_name);
        $this->assertEquals('system', $bees->data->bees[0]->category);
        $this->assertEquals('transfer', $bees->data->bees[0]->bee_icon);
        $this->assertEquals('Send File', $bees->data->bees[0]->label);
        $this->assertEquals('With the share file (transfer) bee you can send documents securely to any user in the world. Everyone will get a full copy of your document not just a link. If they are not part of XcooBee simply provide their email and we will invite them for you and you get extra points when they sign up.', $bees->data->bees[0]->description);
        $this->assertEquals(true, $bees->data->bees[0]->is_file_reader);
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
