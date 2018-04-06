<?php

namespace XcooBee\Core\Api;


use GuzzleHttp\Exception\RequestException;
use XcooBee\Http\FileUploader;

class Bees extends Api
{
    /**
     * Return list of bees
     *
     * @param string $searchText
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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

        return $this->_request($query, ['searchText' => $searchText]);
    }

    /**
     * Upload passed files
     *
     * @param string[] $files
     * @param string $endPoint
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadFiles($files, $endPoint = "outbox")
    {
        $endPoint = !empty($endPoint) ? $endPoint : "outbox";
        $users = new Users();
        $user = $users->getUser();
        
        if($user !== null)
        {
            $endpointCursor = $this->getOutboxEndpoint($user->userCursor, $endPoint);
            
            $policies = $this->getPolicy($endPoint, $endpointCursor, $files);
            
            foreach ($files as $key => $file) {
                try {
                    $policy = "policy" . $key;
                    $policy = $policies->data->$policy;
                    
                    $upload = new FileUploader();

                    return $upload->uploadFile($file, $policy);
                    
                } catch(RequestException $e) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Trigger flight of passed bees
     *
     * @param array $bees
     * @param array $params
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function takeOff(array $bees, $params)
    {
        $query = 'mutation addDirective($params: DirectiveInput!) {
            add_directive(params: $params) {
                ref_id
            }
        }';

        $beeParams = [
            'filenames'         => $params["process"]["fileNames"],
            'user_reference'    => $params["process"]["userReference"]
        ];
        
        $destinations = $params["process"]["destinations"];
        foreach($destinations as $key => $destination){
            if($this->isValidEmail($destination)){
                $beeParams["destinations"][$key] = ["email"=> $destination];
            }
            else{
                $beeParams["destinations"][$key] = ["xcoobee_id"=> $destination];
            }
        }
       
        $beeParams["bees"]= [];
        foreach ($bees as $key => $bee) {
            if($bee !=="transfer"){
                $beeParams["bees"][$key] = [
                    'bee_name'=>$bee, 
                    'params'=> isset($params[$bee]) ? (json_encode($params[$bee])) :'{}',
                ];
            }
        }

        return $this->_request($query, [ 'params' => $beeParams ]);
    }

    private function getPolicy($intent, $endpointCursor = "", $files = [])
    {
        $query = 'query uploadPolicy {';
        foreach($files as $key => $file){
            $fileName = basename($file);

            $query .= "policy$key: upload_policy(filePath: '$fileName',
                intent: '$intent',
                identifier: '$endpointCursor'){
                    signature
                    policy
                    date
                    upload_url
                    key
                    credential
                    identifier
                }";
        }
        $query .= '}';

        return $this->_request($query);
    }

    private function getOutboxEndpoint($userCursor, $intent)
    {
        $query = 'query getEndpoint($user_cursor: String!) {
            outbox_endpoints(user_cursor: $user_cursor) {
                data {
                    cursor
                    name
                    date_c
                }
            }
        }';
        
        $response = $this->_request($query, ['user_cursor' => (string)$userCursor]);

        $endpoint = array_filter($response->data->outbox_endpoints->data,
            function($value, $key) use ($intent) {
                return (($value->name == $intent) || ($value->name == "flex"));
        }, ARRAY_FILTER_USE_BOTH);

        if($endpoint != null ){
            return $endpoint[0]->cursor;
        }

        return null;
    }

    function isValidEmail($value) {
        return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $value);
    }
}