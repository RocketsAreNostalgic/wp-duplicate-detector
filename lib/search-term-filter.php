<?php
namespace OrionRush\DuplicateDetector\Enabled;

if ( ! defined( 'ABSPATH' ) ) die();
if (!is_admin()){ return; }

/**
 * Returns an array with the cleaned-up search string at the zero index and a list of terms in the first.
 * Derived from the better-search plugin v2.1.0
 *
 * @since	0.0.2
 *
 * @author Ajay <https://wordpress.org/plugins/better-search/>
 * @author WebberZone <https://wordpress.org/plugins/better-search/>
 * @author orionrush
 *
 * @param	string   $search_query   The search terms.
 * @return	array	Cleaned up search string as array.
 */
function get_search_terms( $search_query) {

    if ( !( '' == $search_query ) || empty( $search_query ) )

        $search_query = html_entity_decode($search_query, ENT_QUOTES, 'UTF-8' ); // Re encode any entities

        // strip out  many characters that sql might use
        $search_query = preg_replace( '/, +/', ' ', $search_query );
        $search_query = str_replace( ',', ' ', $search_query );
        $search_query = str_replace( '"', ' ', $search_query );
        $search_query = trim( $search_query ); // Clear white space on ends
        $search_words = explode( ' ', $search_query ); // create array

    $s_array[0] = $search_query;	// Save original query string at [0]
    $s_array[1] = $search_words;	// Save array of terms at [1]

    // Log these values if we're debugging
    \OrionRush\DuplicateDetector\write_log("DD Search terms:");
    \OrionRush\DuplicateDetector\write_log($s_array);

    /**
     * Filter array holding the search query and terms
     *
     * @since	0.0.2
     *
     * @param	array	$s_array	Filtered original query is at [0] and array of terms at [1]
     */
    return apply_filters( __NAMESPACE__ . '\\get_search_terms', $s_array );
}

/**
 * Returns an array of posts that match the search terms.
 *
 * @since	0.0.2
 * @author orionrush
 *
 * @param   string  $search_query   Incoming Search String
 * @param   string  $post_id        id of the current post, and a space separated list of any other posts to exclude from search
 * @param   string  $post_status    Restrict to listed post statues, space separated list
 * @param   string  $post_types     Restrict to listed post types, space separated list
 * @param   bool    $bydate         Sort by date?
 *
 * @return string
 */
function get_any_matches( $search_query, $post_id ='', $post_status = '', $post_types = '', $by_date = '0') {
    global $wpdb;

    $search_info = get_search_terms( $search_query);

    $sql = sql_prepare( $search_info, $post_id, $post_status, $post_types, $by_date);
    $results = $wpdb->get_results( $sql );

    // Log these values if we're debugging
    \OrionRush\DuplicateDetector\write_log("DD SQL Query:");
    \OrionRush\DuplicateDetector\write_log($sql);

    \OrionRush\DuplicateDetector\write_log("DD Results:");
    \OrionRush\DuplicateDetector\write_log($results);

    return $results;
}