<?php

namespace Test\XcooBee;


use XcooBee\Core\Encryption;
use XcooBee\Models\ConfigModel;
use XcooBee\Test\TestCase;

class EncryptionTest extends TestCase
{
    /**
     * @expectedException \XcooBee\Exception\EncryptionException
     */
    public function testDecrypt_NoPrivateKeyOrPgpPassProvided()
    {
        $xcoobee = $this->_getXcooBeeMock(['getConfig' => new ConfigModel()]);

        $encryption = new Encryption($xcoobee);

        $encryption->decrypt("test");
    }

    public function testDecrypt_DamagedMessage()
    {
        $xcoobee = new \XcooBee\XcooBee();
        $xcoobee->setConfig(ConfigModel::createFromFile(__DIR__ . '/../../../assets/valid-config-with-pgp'));

        $encryption = new Encryption($xcoobee);

        $this->assertNull($encryption->decrypt("test"));
    }

    public function testDecrypt_DecodeMessage()
    {
        $xcoobee = new \XcooBee\XcooBee();
        $xcoobee->setConfig(ConfigModel::createFromFile(__DIR__ . '/../../../assets/valid-config-with-pgp'));

        $encryption = new Encryption($xcoobee);

        $this->assertEquals('test msg', $encryption->decrypt('-----BEGIN PGP MESSAGE-----
Version: OpenPGP v2.0.8
Comment: https://sela.io/pgp/

wcBMAwkFFKGpsUUhAQgAi3waz6sPVbYLfBZ5l8OpvNgkd6055dw1sl6+oSf8huIq
VXhHRFOlP5GyMVYnPc1p1RGgWXGHLKCTbAYMKaFuSwc1OYfApMU8xoJoRZDeebWc
hw3t/aWGntM4loCvixMcFaUjBpZW6BcVqYqfHbNtdvM1lyF5pS1/qyES6XtJdqvg
K37b82Lthctziof2tZ1VXz5mEhew4o2P1s5CqNwxvBueC3i8XoihdxGYPvIwEjkV
Sd7U1USIWEO9FpFHXNhrmCt6jwNPY9CqUAnp1QIIJbQUP48Y5ZRjpSNrcBFKjFlp
NtCG/trrLNXg9G44F1ACMqqXfh117JFgYria7hAo89JEAfNtuTzI73nub06uRtgW
vobGgpRMzJBfwrbOOWlvuUnFEVg9QPlpI96v+lBWGYnlq3kVeEyauIJSJ75J1osb
6y8cGlw=
=OXj9
-----END PGP MESSAGE-----
'
        ));
    }
}