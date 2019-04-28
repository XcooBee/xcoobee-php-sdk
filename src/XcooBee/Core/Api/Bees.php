<?php

namespace XcooBee\Core\Api;

use XcooBee\Core\Validation;
use XcooBee\Http\FileUploader;
use XcooBee\XcooBee;
use XcooBee\Http\Response;
use GuzzleHttp\Exception\ClientException;

class Bees extends Api
{
    /** @var FileUploader */
    protected $_fileUploader;

    public function __construct(XcooBee $xcoobee)
    {
        parent::__construct($xcoobee);

        $this->_fileUploader = new FileUploader($xcoobee);
    }

    /**
     * Return list of bees
     *
     * @param string $searchText
     * @param array $config
     * @return Response
     * @throws XcooBeeException
     */
    public function listBees($searchText = null, $config = []){
        $query = 'query getBees($searchText: String, $first : Int, $after: String) {
            bees(search: $searchText, first : $first , after : $after) {
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

        return $this->_request($query, ['first' => $this->_getPageSize($config), 'after' => null, 'searchText' => $searchText], $config);
    }

    /**
     * Upload passed files
     *
     * @param string[] $files
     * @param string $endpoint
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function uploadFiles($files, $endpoint = 'outbox', $config = [])
    {
        $endpoint = !$endpoint ? $endpoint : 'outbox';
        $response = new Response();
        $errors = [];
        foreach ($files as $file) {
            if (!file_exists($file)) {
                $errors[] = (object) ["message" => "Invalid File", 'filePath' => $file];
            }
        }

        if ($errors) {
            $response->code = 400;
            $response->errors = $errors;

            return $response;
        }

        $user = $this->_xcoobee->users->getUser($config);
        $endpointId = $this->_getOutboxEndpoint($user->userId, $endpoint, $config);
        $policies = $this->_getPolicy($endpoint, $endpointId, $files, $config);
        if ($policies->errors) {
            $response->code = 400;
            $response->errors = $policies->errors;

            return $response;
        }
        
        foreach ($files as $key => $file) {
            $policy = 'policy' . $key;
            $policy = $policies->result->$policy;
            try {
                $this->_fileUploader->uploadFile($file, $policy);
            }catch (ClientException $exception) {
                $errors[] = (object) ['message' => $exception->getMessage()];
            }
        }

        $response->code = $errors ? 400 : 200;
        if(!$errors) {
            $response->result = true;
        }
        $response->errors = $errors;

        return $response;
    }

    /**
     * Trigger flight of passed bees
     *
     * @param array $bees
     * @param array $options
     * @param array $subscriptions
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function takeOff(array $bees, $options, $subscriptions = [], $config = [])
    {
        $query = 'mutation addDirective($params: DirectiveInput!) {
            add_directive(params: $params) {
                ref_id
            }
        }';

        $params = [
            'user_reference'    => array_key_exists('userReference', $options['process'])
                ? $options['process']['userReference']
                : null,
        ];

        if (array_key_exists('fileNames', $options['process'])) {
            $params['filenames'] = $options['process']['fileNames'];
        }

        if (array_key_exists('custom', $options)) {
            $params['custom'] = $options['custom'];
        }

        if ($subscriptions) {
            $params['subscriptions'] = $subscriptions;
        }

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
                    'bee_name' => $beeName,
                    'params'   => count($beeParams) ? json_encode($beeParams) : "{}",
                ];
            }
        }

        return $this->_request($query, ['params' => $params], $config);
    }

    protected function _getPolicy($intent, $endpointId = "", $files = [], $config = [])
    {
        $query = 'query uploadPolicy {';
        foreach($files as $key => $file){
            $fileName = basename($file);

            $query .= "policy$key: upload_policy(filePath: \"$fileName\",
                intent: $intent,
                identifier: \"$endpointId\"){
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

        return $this->_request($query, [], $config);
    }

    protected function _getOutboxEndpoint($userId, $intent, $config = [])
    {
        $query = 'query getEndpoint($userId: String!) {
            outbox_endpoints(user_cursor: $userId) {
                data {
                    cursor
                    name
                    date_c
                }
            }
        }';
        
        $response = $this->_request($query, ['userId' => (string)$userId], $config);

        $endpoint = array_filter($response->result->outbox_endpoints->data,
            function($value) use ($intent) {
                return (($value->name == $intent) || ($value->name == "flex"));
            });

        if($endpoint != null ){
            return $endpoint[0]->cursor;
        }

        return null;
    }
}
