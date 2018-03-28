<?php namespace xcoobee\core;

use xcoobee\http\Client;
use xcoobee\core\Constants;

class Bees
{
    public function getBees($searchText = ""){
        $query = 'query getBees($searchText: String) {
            bees(search: $searchText) {
                data {
                    cursor
                    bee_system_name
                    category
                    bee_icon
                    label
                    description
                    input_extensions
                    output_extensions
                    input_file_types
                    output_file_types
                    is_file_reader
                }
                page_info {
                    end_cursor
                    has_next_page
                }
            }
        }';
        $headers = [
            'Content-Type' => 'application/json'
        ];
        
        $variables = [
            'searchText' => $searchText
        ];

        $client = new Client;

        return $client->response("graphql", $query, $variables, $headers);
    }

}