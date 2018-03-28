<?php 
use xcoobee\models\configModel;
use xcoobee\core\Message;

class MessageTest extends PHPUnit_Framework_TestCase{
    
    public function testSendUserMessage(){
        $config = new ConfigModel;
        
        $config->apiKey = "sss";
        $config->apiSecret = "sss";
        $config->pgpSecret = "ssss";
        $config->campaignId = "campaignid";

        $message = new Message;
        $msg = null;
        $consentId = "consentId";
        
        $this-> assertEquals("hello ".$msg, $message-> sendUserMessage($msg, $consentId, $config));
    }

    /**
     * @expectedException InvalidArgumentException
     *
     */
    public function throwsExceptionIfParamIsNotSupplied(){
        $message = new Message;
        $this-> assertEquals("hello ", $message-> sendUserMessage());
    }
}