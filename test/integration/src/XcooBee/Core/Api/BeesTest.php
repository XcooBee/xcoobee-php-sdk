<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\IntegrationTestCase;

class BeesTest extends IntegrationTestCase
{
    public function testListBees()
    {
        $bees = $this->_xcoobee->bees->listBees();
        $this->assertEquals(200, $bees->code);
    }
    
    public function testUploadFiles()
    {
        $this->_xcoobee->bees->uploadFiles([__DIR__ . '/../../../../assets/testfile.txt']);
    }
}
