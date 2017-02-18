/*
 * Handles the UI and ajax request which allow users to check for duplicate titles
 *
 * @since   0.0.1
 * @version 0.0.6b
 * @orionrush
 *
 */

function htmlEntities(str) {
    return String(str).replace(/\&/g, '&amp;').replace(/</g, '&lt;').replace(/\>/g, '&gt;').replace(/\"/g, '&quot;').replace(/\'/g, '&#039;').replace(/\‘/g, '&lsquo;').replace(/\’/g, '&rsquo;').replace(/\“/g, '&ldquo;').replace(/\”/g, '&rdquo;');
}
jQuery(document).ready(function () {
    jQuery('#titlediv #title').after('<button type="button" title="' + object_DD.button_notice + '" value="clickme" onclick="jQuery.dupetitles();" id="duplicates" class="duplicates button">D</button>');
    /*
    *  Class switcher
    *  Watches the title input for key up events and triggers a button to prompt the user to check for duplicates
    *  Inspired by http://stackoverflow.com/a/6258628
    *
    * @since    0.0.3
    * @author   orionrush
    */
    function tog(v){return v? 'addClass' : 'removeClass';}

    jQuery(document)
      .on('input', '#titlediv #title', function(){
        jQuery(this)[tog(this.value)]('x'); // adds the icon
      })
      // detect mouse over button
      .on('mousemove', '.x', function( e ){
        jQuery(this)[tog(this.offsetWidth-40 < e.clientX-this.getBoundingClientRect().left)]('onX'); // removes icon & toggles the click-able zone.
      })
      // detect click on button
      .on('click', '.onX', function(){
        jQuery(this).removeClass(function() {
            object_DD.debug ? console.log( "Handler for .change() called." ) : '';
          jQuery.dupetitles();
          return jQuery( this ).attr( "class" );
        }).change();
      })
      .on('keyup', function( ){
        jQuery(this)[tog(this.value)]('x'); // also adds the icon
          jQuery('#titlediv #title').addClass('dd_warning').removeClass("dd_spinner dd_halt dd_check");

      });

    /*
    * Sets up the params and ui prior to the ajax query
    *
    * @since    0.0.2
    * @author   orionrush
    */
    jQuery.dupetitles = function dupetitles() {
        // seed vars
        var title = htmlEntities(jQuery('#title').val().trim()); // trim leading & trailing white space, then encode
        var id = jQuery('#post_ID').val();
        var post_type = jQuery('#post_type').val();

          // Prevent ajax request on empty field
        if (title){
            // add the spinner, cant add spinner via class to disabled field, as bkground is locked?
            var imageUrl = object_DD['plugin_url'] + 'assets/imgs/wpspin_light.gif';
            jQuery('#titlediv #title').css({
              'background-image': 'url("' + imageUrl + '")',
              'background-repeat': 'no-repeat',
              'background-position': '97% 50%'
            });


            // Lock the input & button
            jQuery('#titlediv #title').addClass('disabled dd_spinner').removeClass('active dd_check dd_warning dd_halt').prop('readonly', true);
            jQuery('button.duplicates').addClass('disabled').prop('disabled', true);

            // make the ajax call
            makeAjaxCall(title, id, post_type);
        }

        if (!title) {
            jQuery('#title').val('').blur(); // Empty the field
            // Remove any icons and re-enable the field
            jQuery('#titlediv #title').prop('readonly', false).addClass('active').removeClass('disabled dd_check dd_warning dd_halt');
            jQuery('button.duplicates').prop('disabled', false).removeClass('disabled');
        }
    };

    /**
     * Sends a request to wp to query the db for duplicate titles
     *
     * @since   0.0.1
     * @author  orionrush
     *
     * @uses    jQuery.ajax
    */
    function makeAjaxCall(title, id, post_type) {
        object_DD.debug ? console.log('Starting ajax call to WP to find dupe titles') : '';
        var data = {
          action: 'title_check',
          post_title: title,
          post_type: post_type,
          post_id: id
        };
        object_DD.debug ? console.log( 'action: title_check, post_title: ' + title + ', post_type: ' + post_type + ', post_id: ' + id) : '';
        // In wp admin ajaxurl is a js global
        // var ajaxurl = 'wp-admin/admin-ajax.php';
        // http://wordpress.org/support/topic/ajaxurl-is-not-defined#post-1989445
        jQuery.ajax({
              cache: false,
              type: 'POST',
              url: ajaxurl,
              data: data,
              dataType: 'json', // 'text',
              mimeType: 'application/json'
            })
            .done(function(response){
                object_DD.debug ? console.log('logging response: ' + JSON.stringify(response, null, 4)) : '';
              messages(response);

            })
            .fail(function(xml, status, error){
                if (object_DD.debug) {
                    console.log('logging error: ' + ' :: ' + xml + ' :: ' + status + ' :: ' + error);
                    console.log('title: ' + data.post_title + ' post type: ' + data.post_type + ' post id: ' + data.post_id);
                }
                var response = {
                status: 'error',
                html: '<div id="duplicate-error">' + object_DD.error_message + ' ' + error + '.</div>'
              };
              messages(response);
            });
    }

    /**
     * Build the message response from the returned json values
     *
     * @since 0.0.1
     * @author  orionrush
     *
     * @param response
     *
     * @TODO create loop to iterate over the returned json response, and not expect anything specifically
     */
    function messages(response) {
        // remove any old notices
        jQuery('.duptitles').remove();

        // remove spinner & unlock the text field
        jQuery('#titlediv #title').css('background-image', "").prop('readonly', false).addClass('active').removeClass('disabled');
        jQuery('button.duplicates').prop('disabled', false).removeClass('disabled');

        if (response.status === 'true') {
          //error message formatting
          var message;
          message = '<div id="duplicate-warning"><h3>' + response.notice.head_notice + '</h3>';
          message += '<p><strong>' + response.notice.head_text + '</strong></p>';
          message += '<ul>';
          var sim_results = response.posts;
          for (var i = 0; i < sim_results.length; i++) {
              message += '<li>';
              message += '<a href="' + sim_results[i].link + '" >' + sim_results[i].title + '</a>';
              message += '</li>';
          }

          // we found duplicates, here they are
          message += '</ul><p>' + response.notice.foot + '</p></div>';
          jQuery('#titlediv .inside').prepend('<div id=\"dd-message\" class=\"notice notice-error fade duptitles\"><p>' + message + '</p></div>').slideDown('slow', function(){
              jQuery('#titlediv #title').removeClass('dd_spinner dd_warning dd_check').addClass("dd_halt");
          });
        }
        if (response.status === 'too-short') {
            //error message formatting
            var message;
            // console.log(response.notice);
            message = '<p><strong>' + response.notice + '</strong></p>';

            // we found duplicates, here they are
            message += '</p></div>';
            jQuery('#titlediv .inside').prepend('<div id=\"dd-message\" class=\"notice notice-warning fade duptitles\"><p>' + message + '</p></div>').slideDown('slow', function(){
                jQuery('#titlediv #title').removeClass('dd_spinner dd_warning dd_check').addClass("dd_warning");
            });
        }
        if (response.status === 'false') { //Title is unique
            jQuery('#titlediv #title').removeClass('dd_spinner dd_warning dd_halt').addClass("dd_check");
            if ( object_DD.debug ){
                jQuery('#titlediv .inside').prepend('<div id=\"dd-message\" class=\"notice notice-success fade duptitles\"><p><strong>' + response.notice + '</strong></p></div>').slideDown('slow');
            }
        }
        else if (response.status === 'error' || response.status === 'undefined') { // oops we found an error
          jQuery('#titlediv #title').removeClass('dd_spinner dd_warning dd_check').addClass("dd_halt").delay(600, function () {
              jQuery('#titlediv .inside').prepend('<div id=\"dd-message\" class=\"notice notice-error fade duptitles\"><p>' + response.html + '</p></div>').slideDown('slow');
          });
        }
        if ( object_DD.debug ){
            // only show if WP_DEBUG debug is enabled
            jQuery('#titlediv .inside #dd-message').append('<hr /><div id="dd_debug" class="duptitles"><p>WP_DEBUG is enabled: <a href="#" class="dd_expand">SHOW/HIDE</a></p></br><pre class="dd_collapsible">' + JSON.stringify(response, null, 4) + '</pre></div>');
            jQuery('.dd_collapsible').hide();
            jQuery('.dd_expand').click(function(){
                jQuery('.dd_collapsible').slideToggle('slow');
            });
        }
    }
});

