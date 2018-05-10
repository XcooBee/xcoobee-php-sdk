<?php 

namespace XcooBee\Core\Api;

use XcooBee\Store\PersistedData;
use XcooBee\Models\UserModel;
use XcooBee\Exception\XcooBeeException;

class Users extends Api
{
    /**
     * Return current user
     *
     * @return UserModel
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUser(){
        $store = new PersistedData();
        $user = $store->getStore(PersistedData::CURRENT_USER_KEY);
        
        if($user === null){
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
     * @param String $targetId
     * @param String $consentId
     * @param String $message
     * @param String $note_type
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendUserMessage($targetId, $consentId, $message, $note_type = 'consent') {
        $mutation = 'mutation sendUserMessage($config: SendMessageConfig) {
                send_message(config: $config) {
                    note_text,
                }
            }';

        return $this->_request($mutation, ['config' => ['note_type' => $note_type, 'user_cursor' => $targetId, 'consent_cursor' => $consentId, 'message' => $message]]);
    }
    
    /**
     * list all the user conversation
     *
     * @param array $data
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConversations($data = array(), $first = null, $after=null) {
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

        return $this->_request($query, array_merge(['first' => $first , 'after'=> $after , 'userId' => $this->_getUserId()], $data));
    }

    /**
     * get conversation data
     *
     * @param string $conversationID
     * @param Int $records
     * 
     * @return \XcooBee\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConversation($conversationID, $first = null, $after=null) {
        if (!$conversationID) {
            throw new XcooBeeException('No "conversation" provided');
        }

        $query = 'query getConversation($conversationID: String!,$first : Int, $after: String) {
		conversation(target_cursor : $conversationID, first : $first, after : $after) {
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

        return $this->_request($query, ['first' => $first, 'after'=> $after,'conversationID' => $conversationID]);
    }
}
