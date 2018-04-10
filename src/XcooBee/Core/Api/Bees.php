<?php

namespace XcooBee\Core\Api;


use XcooBee\Core\Validation;
use XcooBee\Http\FileUploader;

class Bees extends Api
{
    /** @var Users */
    protected $_users;
    /** @var FileUploader */
    protected $_fileUploader;

    public function __construct()
    {
        parent::__construct();

        $this->_users = new Users();
    }

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
    public function uploadFiles($files, $endPoint = 'outbox')
    {
        $endPoint = !empty($endPoint) ? $endPoint : 'outbox';

        $user = $this->_users->getUser();
        $endpointCursor = $this->_getOutboxEndpoint($user->userCursor, $endPoint);
        $policies = $this->_getPolicy($endPoint, $endpointCursor, $files);

        $result = [];
        foreach ($files as $key => $file) {
            $policy = 'policy' . $key;
            $policy = $policies->data->$policy;

            $result[] = $this->_fileUploader->uploadFile($file, $policy);
        }

        return $result;
    }

    /**
     * Trigger flight of passed bees
     *
     * @param array $bees
     * @param array $options
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function takeOff(array $bees, $options)
    {
        $query = 'mutation addDirective($params: DirectiveInput!) {
            add_directive(params: $params) {
                ref_id
            }
        }';

        $params = [
            'filenames'         => $options['process']['fileNames'],
            'user_reference'    => $options['process']['userReference']
        ];
        
        $destinations = array_key_exists('destinations', $options['process']) ? $options['process']['destinations'] : [];
        foreach($destinations as $key => $destination) {
            if (Validation::isValidEmail($destination)) {
                $params['destinations'][$key] = ['email' => $destination];
            } else {
                $params['destinations'][$key] = ['xcoobee_id' => $destination];
            }
        }
       
        $params['bees'] = [];
        foreach ($bees as $beeName => $beeParams) {
            if($beeName !== 'transfer'){
                $params['bees'][] = [
                    'bee_name'  => $beeName,
                    'params'    => count($beeParams) ? json_encode($beeParams) : "{}",
                ];
            }
        }

        return $this->_request($query, ['params' => $params]);
    }

    protected function _getPolicy($intent, $endpointCursor = "", $files = [])
    {
        $query = 'query uploadPolicy {';
        foreach($files as $key => $file){
            $fileName = basename($file);

            $query .= "policy$key: upload_policy(filePath: \"$fileName\",
                intent: $intent,
                identifier: \"$endpointCursor\"){
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

    protected function _getOutboxEndpoint($userCursor, $intent)
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
}