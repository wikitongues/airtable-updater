<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       wikitongues.org
 * @since      1.0.0
 *
 * @package    Airtable_Updater
 * @subpackage Airtable_Updater/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Airtable_Updater
 * @subpackage Airtable_Updater/admin
 * @author     Wikitongues <smrohrer@alumni.cmu.edu>
 */
class Airtable_Updater_Admin {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Airtable_Updater_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Airtable_Updater_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/airtable-updater-admin.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Airtable_Updater_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Airtable_Updater_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_register_script( 'admin_script', plugin_dir_url( __FILE__ ) . 'js/airtable-updater-admin.js', array('jquery') );
    wp_localize_script( 'admin_script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
 
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'admin_script' );
  }

  /**
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    1.0.0
   */

  public function add_plugin_admin_menu()
  {

    /*
    * Add a settings page for this plugin to the Settings menu.
    *
    * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
    *
    *        Administration Menus: http://codex.wordpress.org/Administration_Menus
    *
    */

    add_menu_page(
      'Airtable Site Updater',
      'Airtable',
      'publish_posts',
      $this->plugin_name,
      array($this, 'display_plugin_setup_page')
    );
  }

  /**
   * Add settings action link to the plugins page.
   *
   * @since    1.0.0
   */

  public function add_action_links($links)
  {
    /*
    *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
    */
    
    $settings_link = array(
      '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', $this->plugin_name) . '</a>',
    );
    return array_merge($settings_link, $links);
  }

  /**
   * Render the settings page for this plugin.
   *
   * @since    1.0.0
   */

  public function display_plugin_setup_page()
  {
    include_once('partials/airtable-updater-admin-display.php');
  }

  /**
   * Read a CSV file to an associative array
   */
  public static function read_csv($path, $max_length=1000, $delimiter=',')
  {
    if (($handle = fopen($path, 'r')) !== false) {
      $headers = fgetcsv($handle, $max_length, $delimiter);
      $n_fields = count($headers);
      
      $entries = array();
      while (($data = fgetcsv($handle, $max_length, $delimiter)) !== false) {
        $entry = array();
        for ($i = 0; $i < $n_fields; $i++) {
          $entry[$headers[$i]] = $data[$i];
        }

        // Set unique ID
        $entry['ID'] = $entry[$headers[0]];

        $entries[] = $entry;
      }
      fclose($handle);
      return $entries;
    } else {
      return false;
    }
  }

  /**
   * Read a CSV file and update posts
   */
  public static function update_posts_from_csv($path, $max_length=1000, $delimiter=',') 
  {
    $entries = self::read_csv($path, $max_length, $delimiter);
    if ($entries !== false) {
      foreach ($entries as $entry) {
        self::add_post($entry);
      }
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * Query Airtable and update posts
   */
  public static function update_posts_from_airtable($workflow_id)
  {
    $workflows = get_option('workflows');
    $workflow = $workflows[$workflow_id];

    if ($workflow->status == 'In progress') {
      return true;
    }
    
    $workflow->status = 'In progress';
    $workflow->posts_updated = 0;

    $query = new Airtable_Query(
      $workflow->api_url,
      $workflow->base_id,
      rawurlencode($workflow->table),
      rawurlencode($workflow->view),
      $workflow->api_key);
    
    wp_schedule_single_event(time(), 'add_posts', array($query, $workflow_id, null, $workflow->primary_key));

    update_option('workflows', $workflows);

    return true;
  }

  private static $debug_message = '';

  /**
   * Query Airtable and add posts
   */
  public static function add_posts($query, $workflow_id, $offset=null, $primary_key='ID') {
    $workflows = get_option('workflows');
    $workflow = $workflows[$workflow_id];

    $result = $query->do_query($offset);

    if ($result === false) {
      $message = 'Airtable API query failed';
      airtable_updater_log($message);
  
      $workflow->status = 'Error';
      update_option('workflows', $workflows);
      return;
    }

    foreach ($result['records'] as $record) {  
      if (self::is_cancelled($workflow_id)) {
        $message = 'Workflow '.$workflow_id.' was cancelled';
        airtable_updater_log($message);
  
        $workflow->status = 'Cancelled';
        update_option('workflows', $workflows);
        update_option('cancelled_workflow_id', -1);
        return;
      }
  
      self::add_post($record['fields'], $primary_key);
      $workflow->posts_updated++;

      $message = $workflow->posts_updated.' posts updated';
      airtable_updater_log($message);

      update_option('workflows', $workflows);
    }

    if (array_key_exists('offset', $result)) {
      wp_schedule_single_event(time(), 'add_posts', array($query, $workflow_id, $result['offset'], $primary_key));
    } else {
      $workflow->status = 'Done';
      update_option('workflows', $workflows);
    }
  }
  
  private static function is_cancelled($workflow_id) {
    return get_option('cancelled_workflow_id') == $workflow_id;
  }

  /**
   * Add or update post
   */
  public static function add_post($entry, $primary_key='ID') {
    // ID from Airtable
    $wt_id = $entry[$primary_key];

    $message = 'Updating post '.$wt_id;
    airtable_updater_log($message);

    // Find posts with this ID
    $args = array(
      'post_type' => $entry['post_type'],
      'meta_query' => array(
        array(
          'key' => 'wt_id',
          'value' => $wt_id,
          'compare' => 'LIKE'
        )
      )
    );
    $existing_posts = get_posts($args);
    
    if (!empty($existing_posts)) {
      // Post already exists; set ID to post ID assigned by Wordpress
      $entry['ID'] = $existing_posts[0]->ID;
    }

    // Create or update post
    $post_id = wp_insert_post($entry);
    
    // Update custom fields
    foreach ($entry as $field=>$value) {
      $field_obj = acf_maybe_get_field($field, false, false);

      if (is_array($field_obj) && $field_obj['type'] == 'post_object') {
        // Update post object field
        // Airtable API sends value as array
        $titles = is_array($value) ? $value : explode(',', $value);
        $ids = array();
        foreach ($titles as $title) {
          // Find post ID assigned by Wordpress given the post title
          $post = get_page_by_title(trim($title), OBJECT, $field_obj['post_type']);
          if ($post != null) {
            array_push($ids, $post->ID);
          }
        }
        // Set field to array of post ID's
        update_field($field, $ids, $post_id);
      } else {
        update_field($field, $value, $post_id);
      }
    }

    if (empty($existing_posts)) {
      // If this is a new post, assign the ID from Airtable in metadata
      update_field('wt_id', $wt_id, $post_id);
    }
  }

  /**
   * Turn the scheduled action on or off
   */
  public static function set_scheduled_post($workflow_id)
  {
    $workflows = get_option('workflows');
    $workflow = $workflows[$workflow_id];

    $args = array($workflow_id);

    if ($workflow->scheduled) {
      wp_schedule_event($workflow->timestamp, $workflow->frequency, 'admin_scheduled_update', $args);
    } else {
      wp_clear_scheduled_hook('admin_scheduled_update', $args);
    }
  }

  /**
   * Perform the scheduled action
   */
  public static function scheduled_update($workflow_id)
  {
    $workflows = get_option('workflows');

    if (!array_key_exists($workflow_id, $workflows)) {
      $args = array($workflow_id);
      wp_clear_scheduled_hook('admin_scheduled_update', $args);
      return;
    }

    self::update_posts_from_airtable($workflow_id);
  }

  /**
   * Define minutely recurrence frequency
   * TODO remove
   */
  public static function define_minutely($schedules)
  {
    $schedules['minutely'] = array(
      'interval' => 60,
      'display' => __('Every minute')
    );

    return $schedules;
  }

  /**
   * Define monthly recurrence frequency
   */
  public static function define_monthly($schedules)
  {
    $schedules['monthly'] = array(
      'interval' => 2592000,
      'display' => __('Once every 30 days')
    );

    return $schedules;
  }

  public function options_update() {
    register_setting($this->plugin_name, 'workflows');
    register_setting($this->plugin_name, 'selected_workflow');
    register_setting($this->plugin_name, 'cancelled_workflow_id');
  }
  
  public static function refresh_workflow() {
    if ( !wp_verify_nonce( $_REQUEST['nonce'], 'refresh_workflow_nonce')) {
      exit('Could not verify nonce');
    }  

    $workflows = get_option('workflows');
    $selected_workflow = get_option('selected_workflow');

    $workflow = $workflows[$selected_workflow];

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($workflow);
      echo $result;
    }
    else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
    }

    die();
  }
}
