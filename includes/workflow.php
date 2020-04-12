<?php

class Workflow {

    public $name;
    public $api_url;
    public $base_id;
    public $table;
    public $view;
    public $api_key;
    public $primary_key;
    public $scheduled;
    public $frequency;
    public $timestamp;

    public $status;
    public $posts_updated;

  function __construct()
  {
      $this->name = 'My Workflow';
      $this->api_url = 'https://api.airtable.com/v0';
	}
	
	function __destruct()
	{
		wp_clear_scheduled_hook('admin_scheduled_update_' . $this->name);
	}
}

?>