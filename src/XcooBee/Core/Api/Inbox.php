<?php

namespace XcooBee\Core\Api;

use XcooBee\Exception\XcooBeeException;
use XcooBee\Http\Response;

class Inbox extends Api
{

    /**
     * List all Items in inbox
     *
     * @param String $startId
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function listInbox($startId = null)
    {
        $query = 'query listInbox($startId: String) {
            inbox (after: $startId){
                data {
                    original_name
                    filename
                    file_size
                    sender {
                       from
                       from_xcoobee_id
                       name
                       validation_score
                    }
                    date
                    trans_id
                    trans_name
                    downloaded
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';
        return $this->_request($query, ['after' => $startId]);
    }

    /**
     * get Items in inbox
     *
     * @param String $filename
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function getInboxItem($filename)
    {
        $query = 'query getInboxItem($userId: String!, $filename: String!) {
            inbox_item(user_cursor: $userId, filename: $filename) {
               download_link
               info {c
                    original_name
                    filename
                    file_size
                    sender {
                        from
                        from_xcoobee_id
                        name
                        validation_score
                    }
                    date
                    trans_id
                    trans_name
                    downloaded
                }
            }
        }';

        return $this->_request($query, ['userId' => $this->_getUserId(), 'filename' => $filename]);
    }

    /**
     * delete Item in inbox
     *
     * @param String $filename
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function deleteInboxItem($filename)
    {
        $query = 'mutation deleteInboxItem($userId: String!, $filename: String!) {
            remove_inbox_item(user_cursor: $userId, filename: $filename) {
               trans_id
            }
        }';

        return $this->_request($query, ['userId' => $this->_getUserId(), 'filename' => $filename]);
    }

}
