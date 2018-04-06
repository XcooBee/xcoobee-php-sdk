<?php

namespace XcooBee\Http;


class FileUploader extends Client
{
    /**
     * Upload file to user folder
     *
     * @param $filePath
     * @param $policy
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadFile($filePath, $policy)
    {
         $url = $policy->upload_url;

         return $this->post($url, [
             'multipart' => [
                 [
                     'name'     => 'key',
                     'contents' => $policy->key,
                 ],
                 [
                     'name'     => 'acl',
                     'contents' => 'private',
                 ],
                 [
                     'name'     => 'X-Amz-meta-identifier',
                     'contents' => $policy->identifier,
                 ],
                 [
                     'name'     => 'X-Amz-Credential',
                     'contents' => $policy->credential,
                 ],
                 [
                     'name'     => 'X-Amz-Algorithm',
                     'contents' => 'AWS4-HMAC-SHA256',
                 ],
                 [
                     'name'     => 'X-Amz-Date',
                     'contents' => $policy->date,
                 ],
                 [
                     'name'     => 'Policy',
                     'contents' => $policy->policy,
                 ],
                 [
                     'name'     => 'X-Amz-Signature',
                     'contents' => $policy->signature,
                 ],
                 [
                     'name'     => 'file',
                     'contents' => fopen($filePath, 'r'),
                 ],
         ]]);
    }
}