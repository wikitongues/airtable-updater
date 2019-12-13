<?php

class Workflow {

    public $name;
    public $api_url;
    public $base_id;
    public $table;
    public $views;
	public $api_key;
	public $primary_key;
    public $scheduled;
    public $frequency;
    public $timestamp;

    function __construct()
    {
        $this->name = 'My Workflow';
        $this->api_url = 'https://api.airtable.com/v0';
	}
	
	function __destruct()
	{
		wp_clear_scheduled_hook('admin_scheduled_update_' . $this->name);
	}

	/**
	 * Query Airtable and update posts
	 */
	public function update_posts_from_airtable()
	{
		foreach (explode(';', $this->views) as $view) {
			$query = new Airtable_Query(
				$this->api_url,
				$this->base_id,
				rawurlencode($this->table),
				rawurlencode($view),
				$this->api_key);
			
			wp_schedule_single_event(time(), 'add_posts', array($query, null, $this->primary_key));

			return true;
		}
	}
}

?>