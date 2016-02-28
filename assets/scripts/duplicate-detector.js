/*
 * Handles the UI and ajax request which checks for duplicate titles prior to post save
 * @author  Ben Rush
 * @since   0.0.1
 * @version 0.0.6
 */

jQuery(document).ready(function () {
  jQuery('#titlediv #title').after('<button type="button" title="' + objectL10n.button_notice + '" value="clickme" onclick="jQuery.dupetitles();" id="duplicates" class="duplicates button">D</button>');
  /*
   * Watches the title input for key up events and triggers a button to prompt the user to check for duplicates
   * Inspired by http://stackoverflow.com/a/6258628
   * @since 0.0.3
   */

  // Class switcher
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
          console.log( "Handler for .change() called." );
          jQuery.dupetitles();
          return jQuery( this ).attr( "class" );
        }).change();
      })
      .on('keyup', function( ){
        jQuery(this)[tog(this.value)]('x'); // also adds the icon

        jQuery('#titlediv #title').css({
          'background-image': 'url("/wp-content/plugins/duplicate-detector/assets/imgs/warning.gif")',
          'background-repeat': 'no-repeat',
          'background-position': '99% 50%'
        });
      });

  /*
   * Sets up the params and ui prior to the ajax query
   *
   * @since 0.0.2
   */
  jQuery.dupetitles = function dupetitles() {

    // seed vars
    var title = jQuery('#title').val().trim(); // trim leading & trailing white space
    var id = jQuery('#post_ID').val();
    var post_type = jQuery('#post_type').val();

      // Prevent ajax request on empty field
    if (title){
        // add the spinner
        var imageUrl = '/wp-admin/images/wpspin_light.gif';
        jQuery('#titlediv #title').css({
          'background-image': 'url("' + imageUrl + '")',
          'background-repeat': 'no-repeat',
          'background-position': '98% 50%'
        });
        // Lock the input & button
        jQuery('#titlediv #title').prop('readonly', true).addClass('disabled').removeClass('active');
        jQuery('button.duplicates').prop('disabled', true).addClass('disabled');

        // make the ajax call
        makeAjaxCall(title, id, post_type);
    }

    if (!title) {
        jQuery('#title').val('').blur(); // Empty the field
        // Remove any icons and re-enable the field
        jQuery('#titlediv #title').css('background-image', "").prop('readonly', false).addClass('active').removeClass('disabled');
    }
  };

  /**
   * Sends a request to wp to query the db for duplicate titles
   *
   * @uses jQuery.ajax
   * @since 0.0.1
   */
  function makeAjaxCall(title, id, post_type) {
    console.log('Starting ajax call to WP to find dupe titles');
    var data = {
      action: 'title_check',
      post_title: title,
      post_type: post_type,
      post_id: id
    };

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
          console.log('logging response: ' + JSON.stringify(response, null, 4));
          messages(response);

        })
        .fail(function(xml, status, error){
          console.log('logging error: ' + ' :: ' + xml + ' :: ' + status + ' :: ' + error);
          console.log('title: ' + data.post_title + ' post type: ' + data.post_type + ' post id: ' + data.post_id);
          var response = {
            status: 'error',
            html: '<div id="duplicate-error" style="color:red;">' + objectL10n.error_message + ' ' + error + '.</div>'
          };
          messages(response);
        });
  }

    /**
     * Build the message response from the returned json values
     *
     * @TODO create loop to iterate over the returned json response, and not expect anything specifically
     *
     * @param response
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
      message = '<div id="duplicate-warning"><h3 style="padding-left: 0; color:red">' + response.notice.head + '</h3>';
      message += '<ul>';
      var sim_results = response.posts;
      for (var i = 0; i < sim_results.length; i++) {
          message += '<li>';
          message += '<a href="' + sim_results[i].link + '" >' + sim_results[i].title + '</a>';
          // Eventually we may want to pass unknown elemets to this array and have them output to the alert screen. ie, what post type etc
          //var trimmed_array = sim_results.splice(2,2);
          //if (trimmed_array){
          //    for (var j = 0; j < trimmed_array.length; j++) {
          //        if(!trimmed_array.hasOwnProperty(j)) continue;
          //        message += ': ' + JSON.stringify(trimmed_array[j]);
          //    }
          //}
          message += '</li>';
      }
      // we found duplicates, here they are
      message += '<p>' + response.notice.foot + '</p></ul></div>';
      jQuery('#titlediv .inside').prepend('<div id=\"message\" class=\"error fade duptitles\"><p>' + message + '</p></div>').slideDown('slow', function(){
        jQuery('#titlediv #title').css({
          'background-image': 'url("/wp-content/plugins/duplicate-detector/assets/imgs/halt.gif")',
          'background-repeat': 'no-repeat',
          'background-position': '99% 50%'
        });
      });
    }
    if (response.status === 'false') { //Title is unique

      jQuery('#titlediv #title').css({
        'background-image': 'url("/wp-content/plugins/duplicate-detector/assets/imgs/check.gif")',
        'background-repeat': 'no-repeat',
        'background-position': '99% 50%'
      });

    }
    else if (response.status === 'error' || response.status === 'undefined') { // oops we found an error

      jQuery('#titlediv #title').css({
        'background-image': 'url("/wp-content/plugins/duplicate-detector/assets/imgs/halt.gif")',
        'background-repeat': 'no-repeat',
        'background-position': '99% 50%'
        }).delay(600, function () {
          jQuery('#titlediv .inside').prepend('<div id=\"message\" class=\"error fade duptitles\"><p>' + response.html + '</p></div>').slideDown('slow');
          //jQuery('#poststuff').prepend('<div id="message" class="error fade duptitles"><pre>' + JSON.stringify(response, null, 4) + '</pre></div>'); //debugging
      });
    }
  }
});