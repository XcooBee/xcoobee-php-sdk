<?php namespace XcooBee\Http;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use XcooBee\Core\Constants;

class FileUploader
{
     /**
     * @var \GuzzleHttp\Client
     *
     */
    protected $guzzle;

    public function __construct()
    {
        $log = new Logger('xcoobee');
        $log->pushHandler(new StreamHandler(__DIR__.'/fileupload.log', Logger::DEBUG));
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($log, new MessageFormatter('{req_headers} --- {url} --- REQ_BODY::{req_body} --- RESPONSE:: {res_body}')));

        $this->guzzle = new \GuzzleHttp\Client([
            'handler' => $stack,
            'timeout' => Constants::TIME_OUT
        ]);
    }

    public function uploadFile($filePath, $policy)
    {
             $url = $policy->upload_url;
 
             return $this->guzzle->post($url, [
                 'multipart' => [
                     [
                         "name" =>"key",
                         "contents" =>$policy->key,
                     ],
                     [
                         "name" =>"acl",
                         "contents" =>"private",
                     ],
                     [
                         "name" =>"X-Amz-meta-identifier",
                         "contents" =>$policy->identifier,
                     ],
                     [
                         "name" =>"X-Amz-Credential",
                         "contents" =>$policy->credential,
                     ],
                     [
                         "name" =>"X-Amz-Algorithm",
                         "contents" =>"AWS4-HMAC-SHA256",
                     ],
                     [
                         "name" =>"X-Amz-Date",
                         "contents" =>$policy->date,
                     ],
                     [
                         "name" =>"Policy",
                         "contents" =>$policy->policy,
                     ],
                     [
                         "name" =>"X-Amz-Signature",
                         "contents" =>$policy->signature,
                     ],
                     [
                         "name" =>"file",
                         "contents" =>fopen($filePath, 'r'),
                     ],
             ]
         ]);
    }
}