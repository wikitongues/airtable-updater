<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              wikitongues.org
 * @since             1.0.0
 * @package           Airtable_Updater
 *
 * @wordpress-plugin
 * Plugin Name:       Airtable Site Updater
 * Plugin URI:        wikitongues.org
 * Description:       Updates the Wikitongues website from Airtable views.
 * Version:           1.0.2
 * Author:            Wikitongues
 * Author URI:        wikitongues.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       airtable-updater
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AIRTABLE_UPDATER_VERSION', '1.0.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-airtable-updater-activator.php
 */
function activate_airtable_updater() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-airtable-updater-activator.php';
	Airtable_Updater_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-airtable-updater-deactivator.php
 */
function deactivate_airtable_updater() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-airtable-updater-deactivator.php';
	Airtable_Updater_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_airtable_updater' );
register_deactivation_hook( __FILE__, 'deactivate_airtable_updater' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-airtable-updater.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_airtable_updater() {

	$plugin = new Airtable_Updater();
	$plugin->run();

}
run_airtable_updater();
