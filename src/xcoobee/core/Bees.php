<?php namespace XcooBee\Core;

use XcooBee\Http\GraphQLClient;
use XcooBee\Core\Constants;
use XcooBee\Core\Users;
use XcooBee\Http\FileUploader;

class Bees
{
    public function getBees($searchText = ""){
        $query = 'query getBees($searchText: String) {
            bees(search: $searchText) {
                data {
                    cursor
                    bee_system_name
                    category
                    bee_icon
                    label
                    description
                    input_extensions
                    output_extensions
                    input_file_types
                    output_file_types
                    is_file_reader
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';
        $headers = [
            'Content-Type' => 'application/json'
        ];
        
        $variables = [
            'searchText' => $searchText
        ];

        $client = new GraphQLClient;

        return $client->response("graphql", $query, $variables, $headers);
    }

    public function uploadFiles($files = [], $endPoint = "outbox")
    {
        $endPoint = !empty($endPoint) ? $endPoint : "outbox";
        $users = new Users;
        $user = $users->getUser();
        
        if($user !== null)
        {
            $endpointCursor = $this->getOutboxEndpoint($user->userCursor, $endPoint);
            
            $policies = $this->getPolicy($endPoint, $endpointCursor, $files);
            
            foreach ($files as $key => $file) {
                try {
                    $fileName = basename($file);
                    $policy="policy".$key;
                    $policy = $policies->data->$policy;
                    
                    $upload = new FileUploader();
                    $response = $upload->uploadFile($file, $policy);
                    
                } catch(RequestException $e) {
                    throw $e;
                }
            }
        }
    }

    public function takeOff($bees=[], $params, $subscriptions)
    {
        $query = 'mutation addDirective($params: DirectiveInput!) {
            add_directive(params: $params) {
                ref_id
            }
        }';

        $beeParams = array(
            'filenames' => $params["process"]["fileNames"],
            'user_reference' => $params["process"]["userReference"]
        );
        
        $destinations = $params["process"]["destinations"];
        foreach($destinations as $key => $destination){
            if($this->isValidEmail($destination)){
                $beeParams["destinations"][$key] = array("email"=> $destination);
            }
            else{
                $beeParams["destinations"][$key] = array("xcoobee_id"=> $destination);
            }
        }
       
        $beeParams["bees"]= array();
        foreach ($bees as $key => $bee) {
            if($bee !=="transfer"){
                $beeParams["bees"][$key] = array(
                    'bee_name'=>$bee, 
                    'params'=> isset($params[$bee]) ? (json_encode($params[$bee])) :'{}',
                );
            }
        }

        $headers = [
            'Content-Type' => 'application/json'
        ];
        
        $variables = [
            'params' => $beeParams
        ];

        $client = new GraphQLClient;

        $response = $client->response("graphql", $query, $variables, $headers);
        print_r($response);
    }

    private function getPolicy($intent, $endpointCursor="", $files=[])
    {
        $client= new GraphQLClient;
        $query = 'query uploadPolicy {';
        foreach($files as $key => $file){
            $fileName = basename($file);
            $query = $query.'policy'.$key.': upload_policy(filePath: "'."$fileName".'",
            intent: '.$intent.',
            identifier: "'."$endpointCursor".'"){
                signature
                policy
                date
                upload_url
                key
                credential
                identifier
            }';
        }
        $query = $query.'}';

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $response = $client->response("graphql", $query, [], $headers);
        return json_decode($response->data);
    }

    private function getOutboxEndpoint($userCursor, $intent)
    {
        $userIntent = $intent;
        $client = new GraphQLClient;
        $query = 'query getEndpoint($user_cursor: String!) {
            outbox_endpoints(user_cursor: $user_cursor) {
                data {
                    cursor
                    name
                    date_c
                }
            }
        }';

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $variables = [
            'user_cursor' => (string)$userCursor
        ];
        
        $response = $client->response("graphql", $query, $variables, $headers);
        $responseClass = json_decode($response->data);
        
        $endpoint = array_filter($responseClass->data->outbox_endpoints->data, 
            function($value, $key) use ($intent) {
                return (($value->name == $intent) || ($value->name == "flex"));
        }, ARRAY_FILTER_USE_BOTH);

        if($endpoint != null ){
            return $endpoint[0]->cursor;
        }

        return null;
    }

    function isValidEmail($value) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $value)) ? false : true;
    }
}