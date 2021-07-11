<?php
function airtable_updater_log ($message) {
  // https://www.smashingmagazine.com/2011/03/ten-things-every-wordpress-plugin-developer-should-know/
  if (WP_DEBUG === true) {
    if (is_array($message) || is_object($message)) {
      error_log(print_r($message, true));
    } else {
        error_log($message);
    }
  }
}
?>