<?php 
namespace XcooBee\Core\Api;
use XcooBee\Core\Api\users;
use XcooBee\Http\response;
use XcooBee\Core\Api\consents;
use XcooBee\Core\Configuration;
class System extends Api
{
	
	public function __construct()
    {
        $this->_users = new Users();
        $this->_consent = new Consents();
        $this->_response = new Response();
    }
    /**
     * ping method
     *
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
     
    public function ping()
    {
        $user = $this->_users->getUser();
		if($user->pgp_public_key){
			$campaignInfo=$this->_consent->getCampaignInfo();
			if(!empty($campaignInfo->data->campaign))
			{
				$this->_response->code=200;
			}else
			{
				$this->_response->code=400;
				$this->_response->errors="campaign not found.";
			}
		}else{
			$this->_response->code=400;
			$this->_response->errors="pgp key not found.";	
		}
		
		return $this->_response;	
    }
}
