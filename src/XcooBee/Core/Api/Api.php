<?php

namespace XcooBee\Core\Api;


use XcooBee\Http\GraphQLClient;
use XcooBee\Store\PersistedData;

class Api
{
    /** @var GraphQLClient */
    protected $_client;

    public function __construct()
    {
        $this->_client = new GraphQLClient();
    }

    /**
     * Returns current user cursor
     *
     * @return string
     */
    protected function _getUserCursor()
    {
        $user = PersistedData::getInstance()->getStore(PersistedData::CURRENT_USER_KEY);

        return $user->userCursor;
    }

    /**
     * Make request to graphQL API
     *
     * @param $query
     * @param array $variables
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function _request($query, $variables = [])
    {
        return $this->_client->request($query, $variables, [
            'Content-Type' => 'application/json',
        ]);
    }
}