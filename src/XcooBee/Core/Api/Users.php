<?php

namespace XcooBee\Core\Api;

use XcooBee\Store\CachedData;
use XcooBee\Models\UserModel;
use XcooBee\Exception\XcooBeeException;
use XcooBee\Http\Response;

class Users extends Api
{
    /**
     * Return current user
     * 
     * @param array $config
     * @return UserModel
     * 
     * @throws XcooBeeException
     */
    public function getUser($config = []) 
    {    
        if($config){
            return $this->_getUser($config);
        }
        
        $store = $this->_xcoobee->getStore();
        $user = $store->getStore(CachedData::CURRENT_USER_KEY);
        if ($user === null) {
            $user = $this->_getUser($config);
            $store->setStore(CachedData::CURRENT_USER_KEY, $user);
        }

        return $user;
    }
    
    protected function _getUser($config)
    {
        $query = 'query {
            user {
                cursor
                xcoobee_id
                pgp_public_key
            }
        }';
        $response = $this->_request($query, [], $config);
        if($response->code !== 200){
            throw new XcooBeeException('invalid "user detail" provided');
        }
        
        $user = new UserModel();
        $user->userId = $response->result->user->cursor;
        $user->xcoobeeId = $response->result->user->xcoobee_id;
        $user->pgp_public_key = $response->result->user->pgp_public_key;
        
        return $user;
    }

    /**
     * Return user's public PGP key.
     *
     * @param String $xid XcooBee ID.
     * @return String|null
     */
    public function getUserPublicKey($xid, $config = [])
    {
        if (!$xid) {
            throw new XcooBeeException('No "user" provided');
        }

        $query = 'query getUserPublicKey($xid: String!) {
                users(xcoobee_id: $xid) {
                    data {
                        pgp_public_key
                    }
                }
            }';

        $response = $this->_request($query, ['xid' => $xid], $config);

        if (!empty($response->result->users->data)) {
            return $response->result->users->data[0]->pgp_public_key;
        }

        return null;
    }

    /**
     * send message to user
     *
     * @param String $message
     * @param String $consentId
     * @param String $breachId
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function sendUserMessage($message, $consentId, $breachId = null, $config = []) 
    {
        $mutation = 'mutation sendUserMessage($config: SendMessageConfig) {
                send_message(config: $config) {
                    note_text,
                }
            }';
        $userId = $this->_getUserIdByConsent($consentId);
        if (!$userId) {
            throw new XcooBeeException('invalid "consent" provided');
        }
        
        $noteType = $breachId ? 'breach' : 'consent';
        return $this->_request($mutation, ['config' => [
                        'note_type'         => $noteType,
                        'user_cursor'       => $userId,
                        'breach_cursor'     => $breachId,
                        'consent_cursor'    => $consentId,
                        'message'           => $message
        ]], $config);
    }

    /**
     * list all the user conversation
     *
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function getConversations($config = []) 
    {
        $query = 'query getConversations($userId: String!, $first : Int, $after: String) {
            conversations(user_cursor: $userId, first : $first , after : $after) {
                data {
                    display_name,
                    consent_cursor,
                    target_cursor,
                    date_c,
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        return $this->_request($query, ['first' => $this->_getPageSize($config), 'after' => null, 'userId' => $this->_getUserId($config)], $config);
    }

    /**
     * get conversation data
     *
     * @param string $userId
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function getConversation($userId, $config = []) 
    {
        if (!$userId) {
            throw new XcooBeeException('No "user" provided');
        }

        $query = 'query getConversation($userId: String!, $first : Int, $after: String) {
		conversation(target_cursor : $userId, first : $first, after : $after) {
                    data {
                        display_name,
                        consent_cursor,
                        target_cursor,
                        date_c,
                    }
                    page_info {
                        end_cursor
                        has_next_page
                    }
		}
	}';

        return $this->_request($query, ['first' => $this->_getPageSize($config), 'after' => null, 'userId' => $userId], $config);
    }

    protected function _getUserIdByConsent($consentId, $config = []) 
    {
        $store = $this->_xcoobee->getStore();

        if ($consent = $store->getConsent($consentId)) {
            return $consent->user_cursor;
        }
        
        $consent = $this->_xcoobee->consents->getConsentData($consentId, $config);
        $consent = $consent->result->consent;

        if($consent){
            $store->setConsent($consentId, $consent);
    
            return $consent->user_cursor;
        }
       
        return false;
    }

}
