<?php namespace xcoobee;
use xcoobee\models\ConfigModel;
use xcoobee\core\Message;

class XcooBee
{
    protected $homeDirectory;
    public function __construct($homedir)
    {
        $this->homeDirectory = $homedir;
    }

    public function sendUserMessage($message, $consentId, ConfigModel $config)
    {
        $message = new Message;
        return $message->sendUserMessage($message, $consentId, $config);
    }
}