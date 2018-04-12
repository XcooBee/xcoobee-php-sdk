<?php namespace XcooBee\Core;
use XcooBee\Http\GraphQLClient;
use XcooBee\Store\PersistedData;
use XcooBee\Core\Constants;
use XcooBee\Models\UserModel;

class Users
{
    public function getUser(){
        $store = new PersistedData;
        $user = $store->getStore(Constants::CURRENT_USER);
        
        if($user == null){
            $query = 'query {
                user{
                    cursor
                    xcoobee_id
                }
            }';
            $headers = [
                'Content-Type' => 'application/json'
            ];
    
            $client = new GraphQLClient;
            $response = $client->response("graphql", $query, [], $headers);
            
            $user = json_decode($response->data);
            $userModel = new UserModel;
            $userModel->userCursor = $user->data->user->cursor;
            $userModel->xcoobeeId = $user->data->user->xcoobee_id;
            
            $store->setStore(Constants::CURRENT_USER, $userModel);
        }

        return $user;
    }
}