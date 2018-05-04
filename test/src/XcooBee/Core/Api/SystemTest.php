<?php

namespace Test\XcooBee\Core\Api;

use XcooBee\Test\TestCase;

class SystemTest extends TestCase
{
	public function testPing()
	{
		$systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
			'_getDefaultCampaignId' => null,
		]);
		$this->_setProperty($systemMock, '_users', $this->_getMock(Users::class, [
			'getUser' => (object) ['pgp_public_key' => 'test']
		]));
		$this->_setProperty($systemMock, '_consent', $this->_getMock(Users::class, [
			'getCampaignInfo' => (object) [
				'data' => (object) [
					'campaign' => (object) [
						'xcoobee_targets' => [],
					],
				],
			]
        ]));

		$response = $systemMock->ping();
		$this->assertEquals(200, $response->code);
	}

	public function testPing_NoCampaignProvided()
	{
		$systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
			'_getDefaultCampaignId' => null,
		]);
		$this->_setProperty($systemMock, '_users', $this->_getMock(Users::class, [
			'getUser' => (object) ['pgp_public_key' => 'test']
		]));
		$this->_setProperty($systemMock, '_consent', $this->_getMock(Users::class, [
			'getCampaignInfo' => (object) [
				'data' => null
			]
        ]));

		$response = $systemMock->ping();
		$this->assertEquals(400, $response->code);
		$this->assertEquals('campaign not found.', $response->errors[0]->message);
	}

	public function testPing_NoPGP()
	{
		$systemMock = $this->_getMock(\XcooBee\Core\Api\System::class, [
			'_getDefaultCampaignId' => null,
		]);
		$this->_setProperty($systemMock, '_users', $this->_getMock(Users::class, [
			'getUser' => (object) ['pgp_public_key' => null]
		]));

		$response = $systemMock->ping();
		$this->assertEquals(400, $response->code);
		$this->assertEquals('pgp key not found.', $response->errors[0]->message);
	}
}
