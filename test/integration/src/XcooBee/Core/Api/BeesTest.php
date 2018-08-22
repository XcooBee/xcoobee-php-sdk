<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class BeesTest extends IntegrationTestCase
{

    public function testListBees()
    {
        $bees = self::$xcoobee->bees->listBees();
        $this->assertEquals(200, $bees->code);
        $this->assertEquals('xcoobee_dropbox_uploader', $bees->result->bees->data[0]->bee_system_name);
    }

    public function testUploadFiles()
    {
        $uploadResponse = self::$xcoobee->bees->uploadFiles([__DIR__ . '/../../../../assets/testfile.txt']);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $uploadResponse[0]);
    }

    public function testTakeOff()
    {  
        $response = self::$xcoobee->bees->takeOff(
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
