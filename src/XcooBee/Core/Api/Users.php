<?php 

namespace XcooBee\Core\Api;


use XcooBee\Store\PersistedData;
use XcooBee\Models\UserModel;

class Users extends Api
{
    /**
     * Return current user
     *
     * @return mixed
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

            $user = $response->data->user;

            $userModel = new UserModel();
            $userModel->userCursor = $user->cursor;
            $userModel->xcoobeeId = $user->xcoobee_id;
            
            $store->setStore(PersistedData::CURRENT_USER_KEY, $userModel);
        }

        return $user;
    }
}