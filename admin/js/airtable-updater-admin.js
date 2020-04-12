(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

   $(window).load(function() {
    var interval = setInterval(function() {
      $.ajax({
        type: 'post',
        dataType: 'json',
        url: myAjax.ajaxurl,
        data: {action: 'refresh_workflow', nonce: nonce},
        success: function(response) {
          if (response.hasOwnProperty('status') && response.hasOwnProperty('posts_updated')) {
            if (response.status === null && response.posts_updated === null) {
              $('#progress').html('Workflow not run yet');
              clearInterval(interval);
            } else {
              $('#progress').html(response.status + '<br>' + response.posts_updated + ' posts updated');
            }

            if (response.status == 'Done') {
              clearInterval(interval);
            }
          } else {
            console.error(response);
            clearInterval(interval);
          }
        },
        error: function(response) {
          console.error(response);
          clearInterval(interval);
        }
      });
    }, 2000);
   });

})( jQuery );
