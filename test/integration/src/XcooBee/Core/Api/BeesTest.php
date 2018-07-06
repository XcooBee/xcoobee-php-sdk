<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class BeesTest extends IntegrationTestCase {

    /**
     * @param int $responseCode
     * @param array $responseData
     * 
     * @dataProvider BeesProvider
     */
    public function testListBees($responseCode, $responseData) {
        $bees = $this->_xcoobee->bees->listBees();
        $this->assertEquals($responseCode, $bees->code);
        $this->assertEquals($responseData, $bees->data->bees->data[0]);
    }

    public function testUploadFiles() {
        $this->_xcoobee->bees->uploadFiles([__DIR__ . '/../../../../assets/testfile.txt']);
    }

    public function testTakeOff() {
        $response = $this->_xcoobee->bees->takeOff([
            'xcoobee_twitter_base' => ['message' => 'Test post'],
                ], [
            'process' => [
                'fileNames' => ['testfile.txt'],
            ],
        ]);
        $this->assertEquals(200, $response->code);
    }

    public function BeesProvider() {
        return [[
        200,
        (object) [
            'cursor' => 'AvPfoQD5u93KL8gkCLRzdSCrkZ8CaNMW/JN1WGRGCG7pQ/Yv0gRqneriQE9iJqLmVzNQKg==',
            'bee_system_name' => 'transfer',
            'category' => 'system',
            'bee_icon' => 'transfer',
            'label' => 'Send File',
            'description' => 'With the share file (transfer) bee you can send documents securely to any user in the world. Everyone will get a full copy of your document not just a link. If they are not part of XcooBee simply provide their email and we will invite them for you and you get extra points when they sign up.',
            'input_extensions' => ['*'],
            'output_extensions' => [],
            'input_file_types' => ['*'],
            'output_file_types' => ['*'],
            'is_file_reader' => 1
        ]
        ]];
    }

}
