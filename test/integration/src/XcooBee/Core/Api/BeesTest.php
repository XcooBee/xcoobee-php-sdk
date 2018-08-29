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
        $this->assertEquals(200, $uploadResponse->code);
    }

    public function testTakeOff()
    {  
        $user = self::$xcoobee->users->getUser();
        $response = self::$xcoobee->bees->takeOff(
            [
                'xcoobee_message' => [
                    'xcoobee_simple_message' => ['message' => 'Test post'], 'recipient' => ['xcoobee_id' => $user->xcoobeeId]
                ]
            ], 
            [
                'process' => [
                    'fileNames' => ['testfile.txt'],
                    'destinations' => [$user->xcoobeeId],
                ],
            ]);
        $this->assertEquals(200, $response->code);
    }

}
