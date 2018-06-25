<?php

namespace XcooBee\Models;


use XcooBee\Exception\XcooBeeException;

class ConfigModel
{
    const CONFIG_FILE = "/.xcoobee/config";
    const PGP_SECRET_FILE = "/.xcoobee/pgp.secret";

    public $apiKey;
    public $apiSecret;
    public $pgpSecret   = null;
    public $pgpPassword = null;
    public $campaignId  = null;
    public $encrypt      = true;

    /**
     * Creates config model from passed params
     *
     * @param $data [
                'apiKey'        => '',
                'apiSecret'     => '',
                'pgpSecret'     => '',
                'pgpPassword'   => '',
                'campaignId'    => '',
                'encrypt'       => true,
            ]
     *
     * @return self
     * @throws XcooBeeException
     */
    public static function createFromData($data)
    {
        if (!array_key_exists('apiKey', $data) || !array_key_exists('apiSecret', $data)) {
            throw new XcooBeeException('Missing "apiKey" or "apiSecret" key');
        }

        $model = new self();

        $model->apiKey      = $data['apiKey'];
        $model->apiSecret   = $data['apiSecret'];

        if (array_key_exists('pgpSecret', $data)) {
            $model->pgpSecret = $data['pgpSecret'];
        }
        if (array_key_exists('pgpPassword', $data)) {
            $model->pgpPassword = $data['pgpPassword'];
        }
        if (array_key_exists('campaignId', $data)) {
            $model->campaignId = $data['campaignId'];
        }
        if (array_key_exists('encrypt', $data)) {
            $model->encrypt = !!$data['encrypt'];
        }

        return $model;
    }

    /**
     * Search for config files in file system and creates config nodel
     *
     * @param $homeDirPath string
     *
     * @return self
     * @throws XcooBeeException
     */
    public static function createFromFile($homeDirPath)
    {
        $configFilePath = $homeDirPath . self::CONFIG_FILE;
        if (!file_exists($configFilePath)) {
            throw new XcooBeeException("File $configFilePath doesn't exist");
        }

        $lines = file_get_contents($configFilePath);
        $lines = preg_split("/\\r\\n|\\r|\\n/", $lines);

        $configArray = [];
        foreach($lines as $line)
        {
            $column = explode("=", $line);
            $configArray[$column[0]] = $column[1];
        }

        if ($configArray['encrypt'] && file_exists($homeDirPath . self::PGP_SECRET_FILE)) {
            $configArray['pgpSecret'] = file_get_contents($homeDirPath . self::PGP_SECRET_FILE);
        }

        return self::createFromData($configArray);
    }
}
