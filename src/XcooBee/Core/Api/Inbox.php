<?php

namespace XcooBee\Core\Api;

use XcooBee\Exception\XcooBeeException;
use XcooBee\Http\Response;
use DateTime;
use DateInterval;

class Inbox extends Api
{

    /**
     * List all Items in inbox
     *
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function listInbox($config = [])
    {
        $query = 'query listInbox($after: String, $first : Int) {
            inbox (after: $after, first : $first){
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
                    downloaded
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        $inboxItems = $this->_request($query, ['after' => null, 'first' => $this->_getPageSize($config)], $config);

        if ($inboxItems->code != 200) {
            return $inboxItems;
        }
        $inboxItems->result->inbox->data = array_map(function($item) {
            return (object) [
                'fileName'          => $item->original_name,
                'messageId'         => $item->filename,
                'fileSize'          => $item->file_size,
                'sender'            => $item->sender,
                'receiptDate'       => $item->date,
                'expirationDate'    => $this->_getExpirationDate($item->date),
                'downloadDate'      => $item->downloaded,
            ];
        }, $inboxItems->result->inbox->data);

        return $inboxItems;
    }

    /**
     * get Items in inbox
     *
     * @param String $messageId
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function getInboxItem($messageId, $config = [])
    {
        $query = 'query getInboxItem($userId: String!, $filename: String!) {
            inbox_item(user_cursor: $userId, filename: $filename) {
               download_link
               info {
                    file_type
                    file_tags
                    user_ref
                }
            }
        }';

        $inboxItem = $this->_request($query, ['userId' => $this->_getUserId($config), 'filename' => $messageId], $config);
        if ($inboxItem->code != 200) {
            return $inboxItem;
        }
        $fields = [
            'file_type' => 'fileType',
            'file_tags' => 'fileTags',
            'user_ref' => 'userRef',
        ];
        $inboxItem->result->inbox_item->info = array_combine($fields, (array) $inboxItem->result->inbox_item->info);

        return $inboxItem;
    }

    /**
     * delete Item in inbox
     *
     * @param String $messageId
     * @param array $config
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function deleteInboxItem($messageId, $config = [])
    {
        $query = 'mutation deleteInboxItem($userId: String!, $filename: String!) {
            remove_inbox_item(user_cursor: $userId, filename: $filename) {
               trans_id
            }
        }';

        return $this->_request($query, ['userId' => $this->_getUserId($config), 'filename' => $messageId], $config);
    }
    
    protected function _getExpirationDate($date)
    {
        $date = new DateTime($date); 
        $date->add(new DateInterval('P30D'));
        return $date->format('Y-m-d\Th:i:sT');
    }
    
}
