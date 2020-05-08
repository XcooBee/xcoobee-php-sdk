<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class BeesTest extends IntegrationTestCase
{
    public function testListBees()
    {
        $bees = self::$xcoobee->bees->listBees();
        $this->assertEquals(200, $bees->code);
        $bee = $bees->result->bees->data[0];
        $this->assertTrue(isset($bee->cursor));
        $this->assertTrue(isset($bee->bee_system_name));
        $this->assertTrue(isset($bee->category));
        $this->assertTrue(isset($bee->bee_icon));
        $this->assertTrue(isset($bee->label));
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
            [],
            [
                'process' => [
                    'fileNames' => ['testfile.txt'],
                    'destinations' => [$user->xcoobeeId],
                ],
            ]);

        $this->assertEquals(200, $response->code);
    }
}
