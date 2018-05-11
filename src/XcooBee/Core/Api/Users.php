<?php

namespace XcooBee\Core\Api;

use XcooBee\Store\PersistedData;
use XcooBee\Models\UserModel;
use XcooBee\Exception\XcooBeeException;
use XcooBee\Core\Api\Consents;

class Users extends Api {

    /**
     * Return current user
     *
     * @return UserModel
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser() {
        $store = new PersistedData();
        $user = $store->getStore(PersistedData::CURRENT_USER_KEY);

        if ($user === null) {
            $query = 'query {
                user {
                    cursor
                    xcoobee_id
                    pgp_public_key
                }
            }';

            $response = $this->_request($query, []);

            $user = new UserModel();
            $user->userId = $response->data->user->cursor;
            $user->xcoobeeId = $response->data->user->xcoobee_id;
            $user->pgp_public_key = $response->data->user->pgp_public_key;
            $store->setStore(PersistedData::CURRENT_USER_KEY, $user);
        }

        return $user;
    }

    /**
     * send message to user
     *
     * @param String $message
     * @param String $consentId
     * @param String $breachid
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendUserMessage($message, $consentId, $breachId = null) {
        $mutation = 'mutation sendUserMessage($config: SendMessageConfig) {
                send_message(config: $config) {
                    note_text,
                }
            }';

        $targetId = $this->_getTargetID($consentId);
        if (!$targetId) {
            throw new XcooBeeException('invalid "consent" provided');
        }
        $noteType = $breachId ? 'breach' : 'consent';
        return $this->_request($mutation, ['config' => [
                        'note_type' => $note_type,
                        'user_cursor' => $targetId,
                        'breach_cursor' => $breachId,
                        'consent_cursor' => $consentId,
                        'message' => $message
        ]]);
    }

    /**
     * list all the user conversation
     *
     * @param Int $first
     * @param Int $after
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConversations($first = null, $after = null) {
        $query = 'query getConversations($userId: String!,$first : Int, $after: String) {
            conversations(user_cursor: $userId , first : $first , after : $after) {
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

        return $this->_request($query, ['first' => $first, 'after' => $after, 'userId' => $this->_getUserId()]);
    }

    /**
     * get conversation data
     *
     * @param string $userId
     * @param Int $first
     * @param Int $after
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConversation($userId, $first = null, $after = null) {
        if (!$userId) {
            throw new XcooBeeException('No "user" provided');
        }

        $query = 'query getConversation($userId: String!,$first : Int, $after: String) {
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

        return $this->_request($query, ['first' => $first, 'after' => $after, 'userId' => $userId]);
    }

    protected function _getTargetID($consentId) {
        $consents = new Consents();
        $consent = $consents->getConsentData($consentId);
        if (!empty($consent->data->consent)) {

            return $consent->data->consent->request_owner_cursor;
        }

        return false;
    }

}
