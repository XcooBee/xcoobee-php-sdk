<?php

namespace XcooBee\Core;


use XcooBee\Exception\EncryptionException;
use XcooBee\XcooBee;

class Encryption
{
    /** @var XcooBee */
    protected $_xcoobee;

    public function __construct(XcooBee $xcoobee)
    {
        $this->_xcoobee = $xcoobee;
    }

    /**
     * Decrypt PGP encrypted message
     *
     * @param string $message
     *
     * @return string|null
     * @throws EncryptionException
     */
    public function decrypt($message)
    {
        $config = $this->_xcoobee->getConfig();

        if (!$config->pgpSecret || !$config->pgpPassword) {
            throw new EncryptionException('PGP private key or PGP pathphrase not provided');
        }

        $encryptedPrivateKey = \OpenPGP_Message::parse(\OpenPGP::unarmor($config->pgpSecret, 'PGP PRIVATE KEY BLOCK'));
        foreach ($encryptedPrivateKey as $package) {
            if (!($package instanceof \OpenPGP_SecretKeyPacket)) {
                continue;
            }
            $key = \OpenPGP_Crypt_Symmetric::decryptSecretKey($config->pgpPassword, $package);

            $msg = \OpenPGP_Message::parse(\OpenPGP::unarmor($message, 'PGP MESSAGE'));

            $decryptor = new \OpenPGP_Crypt_RSA($key);
            $decrypted = $decryptor->decrypt($msg);

            if ($decrypted) {
                foreach ($decrypted as $packet) {
                    if ($packet instanceof \OpenPGP_LiteralDataPacket) {
                        return $packet->data;
                    } elseif ($packet instanceof \OpenPGP_CompressedDataPacket) {
                        foreach ($packet as $subPacket) {
                            if ($subPacket instanceof \OpenPGP_LiteralDataPacket) {
                                return $subPacket->data;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}