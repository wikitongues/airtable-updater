<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       wikitongues.org
 * @since      1.0.0
 *
 * @package    Airtable_Updater
 * @subpackage Airtable_Updater/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php
$workflows = get_option('workflows');

if (isset($_POST['workflow_select']) == '1') {
  // Switching or adding workflow
  if ($_POST['workflow'] == -1) {
    $selected_workflow = uniqid();
    $workflows[$selected_workflow] = new Workflow();
    update_option('selected_workflow', $selected_workflow);
    update_option('workflows', $workflows);
  } else {
    update_option('selected_workflow', $_POST['workflow']);
  }
}

$selected_workflow = get_option('selected_workflow');

if (isset($_POST['delete_workflow'])) {
  // Delete workflow
  unset($workflows[$selected_workflow]);

  $selected_workflow = count($workflows) > 0 ? key($workflows) : -1;

  update_option('workflows', $workflows);
  update_option('selected_workflow', $selected_workflow);
}

$workflow = $selected_workflow == -1 ? new Workflow() : $workflows[$selected_workflow];

if (isset($_POST['save_workflow']) || isset($_POST['do_airtable'])) {
  // Saving workflow settings
  $toggle = $_POST['toggle_scheduled_upload'] == 'on';
  $timestamp = time();

  $frequency_changed = $_POST['frequency'] != $workflow->frequency;
  $schedule_toggled = $toggle != $workflow->scheduled;

  $workflow->name = $_POST['workflow_name'];
  $workflow->scheduled = $toggle;
  $workflow->timestamp = $timestamp;
  $workflow->frequency = $_POST['frequency'];
  $workflow->api_url = $_POST['airtable_url'];
  $workflow->base_id = $_POST['base_id'];
  $workflow->table = $_POST['table'];
  $workflow->views = $_POST['views'];
  $workflow->primary_key = $_POST['primary_key'];
  $workflow->api_key = $_POST['api_key'];

  if ($selected_workflow == -1) {
    // Saving the first workflow
    $selected_workflow = uniqid();
    update_option('selected_workflow', $selected_workflow);
  }

  $workflows[$selected_workflow] = $workflow;

  update_option('workflows', $workflows);

  if ($schedule_toggled) {
    $this->set_scheduled_post($selected_workflow);
  } else if ($frequency_changed) {
    $args = array($selected_workflow);
    wp_clear_scheduled_hook('admin_scheduled_update', $args);
    $this->set_scheduled_post($selected_workflow);
  }
}

// Load workflow settings
$workflow_name = $workflow->name;
$frequency = $workflow->frequency;
$airtable_url = $workflow->api_url;
$base_id = $workflow->base_id;
$table = $workflow->table;
$views = $workflow->views;
$primary_key = $workflow->primary_key;
$api_key = $workflow->api_key;
$toggle = $workflow->scheduled;

$args = array($selected_workflow);
$next_scheduled = date('Y M d H:i e', wp_next_scheduled('admin_scheduled_update', $args));
?>

<?php
if (isset($_POST['do_csv'])) {
  // Path to upload file
  $target_dir = dirname(__FILE__) . '/../uploads/';

  $target_file = $target_dir . basename($_FILES["csv_file"]["name"]);
  $upload_ok = 1;
  $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  // Allow certain file formats
  if ($file_extension != "csv") {
    echo 'Please upload a CSV file.';
    echo '<br>';
    $upload_ok = 0;
  }

  if ($upload_ok) {
    if (!is_dir($target_dir)) {
      mkdir($target_dir);
    }

    if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
      if ($this->update_posts_from_csv($target_file) === false) {
        echo 'Could not read CSV file';
      } else {
        echo 'Done';
      }
    } else {
      echo 'Sorry, there was an error uploading your file.';
    }
  }
} else if (isset($_POST['do_airtable'])) {
  set_time_limit(0);
  $this->update_posts_from_airtable($selected_workflow);
}
?>

<script>
  function deleteWorkflow(event) {
    event.preventDefault();
    if (confirm('Are you sure you would like to delete this workflow?')) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'delete_workflow';
      event.target.appendChild(input);
      event.target.form.submit();
    }
  }
</script>

<div class="wrap">

  <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

  <form method="post" action="" enctype="multipart/form-data">
    <h3><?php esc_attr_e('Workflow:', 'WpAdminStyle'); ?></h3>
    <select name="workflow" onchange="this.form.submit()" id="workflowSelect">
      <?php foreach ($workflows as $workflow_id => $workflow_option) : ?>
        <option <?php if ($workflow_id == $selected_workflow) echo 'selected'; ?> value="<?php echo $workflow_id; ?>">
          <?php echo $workflows[$workflow_id]->name; ?>
        </option>
      <?php endforeach; ?>
      <option value="-1">New workflow...</option>
    </select>
    <input type="hidden" name="workflow_name" id="workflowName" />
    <input type="hidden" name="workflow_select" value="1" />
  </form>

  <?php if (isset($workflow->status)) echo $workflow->status . '<br>' . $workflow->posts_updated . ' posts updated'; ?>

  <?php if ($toggle) echo '<b>Next scheduled update: ' . $next_scheduled . '</b>'; ?>

  <hr>

  <form method="post" action="" enctype="multipart/form-data" id="workflowForm">
    <div id="col-container">
      <div id="col-left">
        <h3>Airtable Settings</h3>
        <fieldset>
          <label>Workflow Name</label><br>
          <input type="text" name="workflow_name" value="<?php echo $workflow_name; ?>" />
        </fieldset>
        <fieldset>
          <label>Airtable API URL</label><br>
          <input type="text" name="airtable_url" value="<?php echo $airtable_url; ?>" />
        </fieldset>
        <fieldset>
          <label>Base ID</label><br>
          <input type="text" name="base_id" value="<?php echo $base_id; ?>" />
        </fieldset>
        <fieldset>
          <label>Table</label><br>
          <input type="text" name="table" value="<?php echo $table; ?>" />
        </fieldset>
        <fieldset>
          <label>View</label><br>
          <input type="text" name="views" value="<?php echo $views; ?>" />
        </fieldset>
        <fieldset>
          <label>Unique ID Field</label><br>
          <input type="text" name="primary_key" value="<?php echo $primary_key; ?>" />
        </fieldset>
        <fieldset>
          <label>API Key</label><br>
          <input type="text" name="api_key" value="<?php echo $api_key; ?>" />
        </fieldset>
        <h4>Scheduled Update</h4>
        <fieldset>
          <legend class="screen-reader-text"><span>input type="radio"</span></legend>
          <label>
            <input type="radio" name="toggle_scheduled_upload" value="on" <?php checked($toggle); ?> />
            <span><?php esc_attr_e('On', 'WpAdminStyle'); ?></span>
          </label><br>
          <label>
            <input type="radio" name="toggle_scheduled_upload" value="off" <?php checked(!$toggle); ?> />
            <span><?php esc_attr_e('Off', 'WpAdminStyle'); ?></span>
          </label>
        </fieldset>

        <h4>Frequency</h4>
        <fieldset>
          <legend class="screen-reader-text"><span>input type="radio"</span></legend>
          <label>
            <input type="radio" name="frequency" value="hourly" <?php checked($frequency, 'hourly'); ?> />
            <span><?php esc_attr_e('Hourly', 'WpAdminStyle'); ?></span>
          </label><br>
          <label>
            <input type="radio" name="frequency" value="twicedaily" <?php checked($frequency, 'twicedaily'); ?> />
            <span><?php esc_attr_e('Twice daily', 'WpAdminStyle'); ?></span>
          </label><br>
          <label>
            <input type="radio" name="frequency" value="daily" <?php checked($frequency, 'daily'); ?> />
            <span><?php esc_attr_e('Daily', 'WpAdminStyle'); ?></span>
          </label><br>
          <label>
            <input type="radio" name="frequency" value="monthly" <?php checked($frequency, 'monthly'); ?> />
            <span><?php esc_attr_e('Monthly', 'WpAdminStyle'); ?></span>
          </label>
        </fieldset>

        <?php submit_button('Save', 'primary', 'save_workflow', TRUE); ?>
      </div>

      <div id="col-right">
        <h3>Update Now</h3>
        <?php submit_button('Update', 'primary', 'do_airtable', TRUE) ?>

        <h3>Update Now from CSV</h3>
        Select CSV file:
        <input type="file" name="csv_file" id="fileToUpload">
        <?php submit_button('Update', 'primary', 'do_csv', TRUE) ?>

        <?php if ($selected_workflow != -1) : ?>
          <h3>Delete Workflow</h3>
          <button class="button-primary" onclick="deleteWorkflow(event)">Delete</button>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>