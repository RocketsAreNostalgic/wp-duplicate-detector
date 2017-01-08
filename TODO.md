#Todo

_For v 0.0.2_
 
* Move jQuery title input filed background icon declarations into css rules, so that we are simply changing class attributes, and not declaring full paths to icon gifs.
* Correct space/tab code style issues

-- We've found that this isn't always possible, as some field are locked by js during the ajax cycle, and so styles cannont be applied to them at those times. JS is able however to 'reach in', so we're sticking to this route for the spinner, especially.


_For v 0.0.3_

* Add a filter to the error output so that it can be modified
* Add PHP Version check to activation and message on failure

_For v 0.0.4_

* Consider adding the option to suspend autosave on all activated post types as an option in admin
* Add option to limit custom post type to searches of its own type only

* When a post title is found to be a close duplicate (similar_text()), it is automatically converted to draft status.
* Added a visual reminder to update the url slug, and a checkbox to "do this automatically"?


_For v 0.0.5_

* Added all strings are included in pot file.
* Rolled the ajax detector function into its own class so that we can extend it. 
* Added the ability to extend the error output to include other fields as needed (add filters).

_For v 0.0.6_
