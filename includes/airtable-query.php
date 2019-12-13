<?php

class Airtable_Query {

    public $base_url;
    public $base_id;
    public $table;
    public $view;
    public $api_key;

    function __construct($base_url, $base_id, $table, $view, $api_key)
    {
        $this->base_url = $base_url;
        $this->base_id = $base_id;
        $this->table = $table;
        $this->view = $view;
        $this->api_key = $api_key;
    }

    // https://github.com/Airtable/airtable.js/blob/master/lib/query.js

    public function do_query($offset=null) {
        $url = $this->base_url . '/' . $this->base_id . '/' . $this->table . '?view=' . $this->view;
        
        if ($offset !== null) {
            $url .= '&offset=';
            $url .= $offset;
        }

		// Initialize a CURL session. 
		$ch = curl_init();  
		
		// Return Page contents. 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		
		//grab URL and pass it to the variable. 
		curl_setopt($ch, CURLOPT_URL, $url); 

		// Attach API key
		$header = array();
		$header[] = 'Authorization: Bearer ' . $this->api_key;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		// Result as JSON string
        $result = curl_exec($ch); 

		// Close CURL session
		curl_close($ch);

		// Decode as associative array
        $arr = json_decode($result, true);

		if ($arr['error']) {
			echo $arr['error']['message'];
			return false;
		}

		return $arr;
    }

    private function each_page($all_records=array(), $offset=null) {
        $result = $this->do_query($offset);

        if ($result === false) {
            return false;
        } else {
            $all_records = array_merge($all_records, $result['records']);
            if ($result['offset']) {
                return $this->each_page($all_records, $result['offset']);
            }
            return $all_records;
        }
    }

    public function get_records() {
        $all_records = $this->each_page();
        return $all_records;
    }
}

?>