<?php namespace xcoobee\core;

use xcoobee\http\GraphQLClient;
use xcoobee\core\Constants;
use xcoobee\core\Users;
use xcoobee\http\FileUploader;

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

    public function uploadFiles($files = [], $endPoint="")
    {
        $users = new Users;
        $user = $users->getUser();
        if($user !== null)
        {
            //get end point cursor;
            $endpointCursor = $this->getOutboxEndpoint($user->userCursor);
            
            //get policies;
            $policies = $this->getPolicy($endPoint, $endpointCursor, $files);
            foreach ($files as $key => $file) {
                try {
                    $fileName = basename($file);
                    $policy="policy".$key;
                    $policy = $policies->data->$policy;
                    
                    $upload = new FileUploader();
                    $response = $upload->uploadFile($file, $policy);
                    echo "Uploaded successfully ".$fileName."<br />";
                } catch(RequestException $e) {
                    throw $e;
                }
            }
        }
    }

    public function takeOff($bees, $params = [], $subscriptions = [])
    {
        $query = 'mutation addDirective($params: DirectiveInput!) {
            add_directive(params: $params) {
                ref_id
            }
        }';

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

        echo $query;
        $response = $client->response("graphql", $query, [], $headers);
        return json_decode($response->data);
    }

    private function getOutboxEndpoint($userCursor)
    {
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
            function($value, $key) {
                return $value->name == "flex";
            }, ARRAY_FILTER_USE_BOTH);

        if($endpoint != null ){
            return $endpoint[0]->cursor;
        }

        return null;
    }
}