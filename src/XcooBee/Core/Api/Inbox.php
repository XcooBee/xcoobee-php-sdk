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
                    downloaded
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';

        $inboxItems = $this->_request($query, ['after' => $startId]);

        if ($inboxItems->code != 200) {
            return $inboxItems;
        }
        $inboxItems->data->inbox->data = array_map(function($item) {
            return [
                'fileName' => $item->original_name,
                'messageId' => $item->filename,
                'fileSize' => $item->file_size,
                'sender' => $item->sender,
                'receiptDate' => $item->date,
                'downloadDate' => $item->downloaded,
            ];
        }, $inboxItems->data->inbox->data);

        return $inboxItems;
    }

    /**
     * get Items in inbox
     *
     * @param String $filename
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function getInboxItem($messageId)
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

        $inboxItem = $this->_request($query, ['userId' => $this->_getUserId(), 'filename' => $messageId]);
        if ($inboxItem->code != 200) {
            return $inboxItem;
        }
        $fields = [
            'file_type' => 'fileType',
            'file_tags' => 'fileTags',
            'user_ref' => 'userRef',
        ];
        $inboxItem->data->inbox_item->info = array_combine($fields, (array) $inboxItem->data->inbox_item->info);

        return $inboxItem;
    }

    /**
     * delete Item in inbox
     *
     * @param String $filename
     * 
     * @return Response
     * @throws XcooBeeException
     */
    public function deleteInboxItem($messageId)
    {
        $query = 'mutation deleteInboxItem($userId: String!, $filename: String!) {
            remove_inbox_item(user_cursor: $userId, filename: $filename) {
               trans_id
            }
        }';

        return $this->_request($query, ['userId' => $this->_getUserId(), 'filename' => $messageId]);
    }

}
