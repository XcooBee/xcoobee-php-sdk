<?php 
namespace XcooBee\Core\Api;

use XcooBee\Http\Response;
use XcooBee\Core\Api\Consents;
use XcooBee\Core\Configuration;

class System extends Api
{
	/** @var Users */
	protected $_users;
	/** @var Consent */
	protected $_consent;

	public function __construct()
	{
		parent::__construct();

		$this->_users = new Users();
		$this->_consent = new Consents();

	}
	
	/**
     * method to check if pgp key and Campaign is correct.
     *
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
	public function ping()
	{
		$user = $this->_users->getUser();
		$response = new Response();
		if ($user->pgp_public_key) {
			$campaignInfo = $this->_consent->getCampaignInfo();
			if (!empty($campaignInfo->data->campaign)) {
				$response->code = 200;
			} else {
				$response->code = 400;
				$response->errors = "campaign not found.";
			}
		} else {
			$response->code = 400;
			$response->errors = "pgp key not found.";
		}

		return $response;	
	}
}
