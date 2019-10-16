<?php

/**
 * Fired during plugin activation
 *
 * @link       wikitongues.org
 * @since      1.0.0
 *
 * @package    Airtable_Updater
 * @subpackage Airtable_Updater/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Airtable_Updater
 * @subpackage Airtable_Updater/includes
 * @author     Wikitongues <smrohrer@alumni.cmu.edu>
 */
class Airtable_Updater_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Reset scheduled action options
		update_option('workflows', array());
		update_option('selected_workflow', -1);
	}

}
