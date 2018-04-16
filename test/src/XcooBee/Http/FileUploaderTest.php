<?php

namespace Test\XcooBee\Http;


use XcooBee\Test\TestCase;

class FileUploader extends TestCase
{
    /**
     * @param object $policy
     *
     * @dataProvider policyProvider
     */
    public function testUploadFile($policy)
    {
        $filePath = __DIR__ . '/../../../assets/test.txt';

        $fileUploaderMock = $this->_getMock(\XcooBee\Http\FileUploader::class, ['post' => null]);
        $fileUploaderMock->expects($this->once())
            ->method('post')
            ->will($this->returnCallback(function ($uri, $data) use ($policy, $filePath) {
                $this->assertEquals($policy->upload_url, $uri);
                $this->assertTrue(array_key_exists('multipart', $data));

                $this->assertEquals('key', $data['multipart'][0]['name']);
                $this->assertEquals($policy->key, $data['multipart'][0]['contents']);

                $this->assertEquals('acl', $data['multipart'][1]['name']);
                $this->assertEquals('private', $data['multipart'][1]['contents']);

                $this->assertEquals('X-Amz-meta-identifier', $data['multipart'][2]['name']);
                $this->assertEquals($policy->identifier, $data['multipart'][2]['contents']);

                $this->assertEquals('X-Amz-Credential', $data['multipart'][3]['name']);
                $this->assertEquals($policy->credential, $data['multipart'][3]['contents']);

                $this->assertEquals('X-Amz-Algorithm', $data['multipart'][4]['name']);
                $this->assertEquals('AWS4-HMAC-SHA256', $data['multipart'][4]['contents']);

                $this->assertEquals('X-Amz-Date', $data['multipart'][5]['name']);
                $this->assertEquals($policy->date, $data['multipart'][5]['contents']);

                $this->assertEquals('Policy', $data['multipart'][6]['name']);
                $this->assertEquals($policy->policy, $data['multipart'][6]['contents']);

                $this->assertEquals('X-Amz-Signature', $data['multipart'][7]['name']);
                $this->assertEquals($policy->signature, $data['multipart'][7]['contents']);

                $this->assertEquals('file', $data['multipart'][8]['name']);
                $this->assertEquals('test', fread($data['multipart'][8]['contents'], filesize($filePath)));

                fclose($data['multipart'][8]['contents']);
            }));

        $fileUploaderMock->uploadFile($filePath, $policy);
    }

    public function policyProvider()
    {
        return [
            [
                (object) [
                    'upload_url'    => 'test',
                    'key'           => 'testKey',
                    'identifier'    => 'testIdentifier',
                    'credential'    => 'testCredential',
                    'date'          => '2018-01-01',
                    'policy'        => 'testPolicy',
                    'signature'     => 'testSignature',
                ],
                (object) [
                    'upload_url'    => 'test2',
                    'key'           => 'testKey2',
                    'identifier'    => 'testIdentifier2',
                    'credential'    => 'testCredential2',
                    'date'          => '2018-01-02',
                    'policy'        => 'testPolicy2',
                    'signature'     => 'testSignature2',
                ]
            ]
        ];
    }
}
