#Todo

_For v 0.0.2_
 
* [DONE] Move jQuery title input filed background icon declarations into css rules, so that we are simply changing class attributes, and not declaring full paths to icon gifs.
-- We've found that this isn't always possible, as some field are locked by js during the ajax cycle, and so styles cannot be applied to them at those times. JS is able however to 'reach in', so we're sticking to this route for the spinner, especially.


_For v 0.0.3_

* [DONE] Add a filter to the responses they can be modified

* [DONE] Add Activation hook for PHP Version check to activation and message on failure

_For v 0.0.4_

* [DONE]  Consider how to handel very short queries 
* - https://wordpress.org/support/topic/causing-huge-mysql-queries/
* - Handel very short queries - on user side JS?, with an alert abut very short queries (less then 3 char?) and reveals a 'force search' button in the notice?
* - Nope, we just did it with a text in the php, and a truncated response prior to the actual query. Not as preferment but pretty quick to implement.  

* [DONE] Correct space/tab code style issues.

* [DONE] Add option in admin to limit searches in a custom post type to its own type (What is the use case for this?, not sure its really needed).

* [DONE] Add a reminder to update the url slug in the response?

* [WONT_IMPLEMENT] When a post title is found to be a close duplicate (similar_text()), it is automatically converted to draft status.

* [WONT_IMPLEMENT] Consider adding the option to suspend autosave on all activated post types as an option in admin

* [DONE] Added the ability to extend the error output to include other fields as needed (add filters).


_For v 0.0.5_

* [DONE] Added all strings are included in pot file.

* [DONE] Modidfied post-type querey to include all posts types that are not specifically isolated.

* [WONT_IMPLEMENT] (At least not at this time ) Roll ajax detector function into its own class so that we can extend it. 

_For v 0.0.6_
* Refine isolated search so that it can optionally be fully isolated from outside searches, or just from within its own seraches, rather then this be the default behavior.
* Consider rest api endpoint instead of wp_ajax_(action).
    * https://tomjn.com/2017/01/23/writing-wp-rest-api-endpoint-2-minutes/
    * https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_(action)
    * https://deliciousbrains.com/comparing-wordpress-rest-api-performance-admin-ajax-php/
* Consider decoupling global wp variables by wrapping them in a class-method rather then calling them directly.