<?php 

namespace XcooBee\Core\Api;


use XcooBee\Store\PersistedData;
use XcooBee\Models\UserModel;

class Users extends Api
{
    /**
     * Return current user
     *
     * @return UserModel
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(){
        $store = new PersistedData();
        $user = $store->getStore(PersistedData::CURRENT_USER_KEY);
        
        if($user === null){
            $query = 'query {
                user {
                    cursor
                    xcoobee_id
                }
            }';
    
            $response = $this->_request($query, []);

            $user = new UserModel();
            $user->userId = $response->data->user->cursor;
            $user->xcoobeeId = $response->data->user->xcoobee_id;
            
            $store->setStore(PersistedData::CURRENT_USER_KEY, $user);
        }

        return $user;
    }
}
