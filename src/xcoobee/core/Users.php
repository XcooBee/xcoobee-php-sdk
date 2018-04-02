<?php namespace xcoobee\core;
use xcoobee\http\GraphQLClient;
use xcoobee\store\PersistedData;
use xcoobee\core\Constants;
use xcoobee\models\UserModel;

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
            //echo $user->data;
            $user = json_decode($response->data);
            // echo $user->data->user->cursor;
            // echo $user->data->user->xcoobee_id;

            //echo $user->cursor;
            $userModel = new UserModel;
            $userModel->userCursor = $user->data->user->cursor;
            $userModel->xcoobeeId = $user->data->user->xcoobee_id;
            
            $store->setStore(Constants::CURRENT_USER, $userModel);
        }

        return $user;
    }
}