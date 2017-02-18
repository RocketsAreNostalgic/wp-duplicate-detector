<?php
namespace OrionRush\DuplicateDetector\Enabled;
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
if ( ! is_admin() ) {
	return;
}

/**
 * Returns the prepared sql query
 * Derived from the better-search plugin v2.1.0
 * @since	0.0.2
 *
 * @author Ajay <https://wordpress.org/plugins/better-search/>
 * @author WebberZone <https://wordpress.org/plugins/better-search/>
 * @author orionrush
 *
 * @param	array   $search_string  Search query (expects sanitised string at [0], and array of terms [1]
 * @param   string  $post_id        A space separated list of post ID's to exclude from search.
 * @param   string  $post_status    A space separated list of post status flags - defaults to 'publish' OR 'inherit'
 * @param   string  $post_types     A space separated list of post types to search
 * @param	bool    $by_date        Sort by date?
 * @return	string  $sql            The sql query to be performed
 */
function sql_prepare( $search_string, $post_id, $post_status, $post_types, $by_date='') {

    // Initialise some variables
    global $wpdb;

    $join = '';
    $groupby = '';
    $n = '%';

    // Inbound search string as array of terms
    $search_terms = $search_string[1];

    // Fields to return
    $fields = 'ID';

    /**
     * Filter the $fields clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $limits  		The ORDER BY clause of the query
     * @param string   $search_string[0]	Search query
     */
    $fields = apply_filters( __NAMESPACE__ . '\\duplicate_detector_post_limits', $fields );


    /**
     * Filter the JOIN clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $join  		The JOIN clause of the query
     * @param string   $search_string[0]	Search query
     */
    $join = apply_filters( __NAMESPACE__ . '\\duplicate_detector_posts_join', $join );


    // Set Up Post Types
    if (!$post_types){
        $post_types = 'post'; // set it as a default if empty
    }
    parse_str( $post_types, $post_type );	// Save post types as an array

    if (count($post_types)> 1){
        $post_types = join( "', '", $post_types );
    }

    $post_types = $wpdb->prepare(
        "AND $wpdb->posts.post_type IN ('%s') ",
        $post_types
    );
    /**
     * Filter the $post_types clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $post_types  		The ORDER BY clause of the query
     * @param string   $search_string[0]	Search query
     */
    $post_types = apply_filters( __NAMESPACE__ . '\\duplicate_detector_post_types', $post_types );


    // Set up post status
    // @TODO: Set up space separated list for OR clause
    if (!$post_status){
        $post_status = "AND (post_status = 'publish' OR post_status = 'inherit')";
    } else {
        $post_status = $wpdb->prepare(
            "AND (post_status = '%s') ",
            $post_status
        );
    }

    /**
     * Filter the post status portion of the query.
     *
     * @since	0.0.2
     *
     * @param string   $post_status  		The ORDER BY clause of the query
     * @param string   $search_string[0]	Search query
     */
    $post_status = apply_filters( __NAMESPACE__ . '\\duplicate_detector_post_status', $post_status );

    // Create the WHERE Clause
    $where = 'AND ( ';
    $where .= $wpdb->prepare(
        " (post_title LIKE '%s') ",
        $n . $search_terms[0] . $n,
        $n . $search_terms[0] . $n
    );

    for ( $i = 1; $i < count( $search_terms ); $i = $i + 1 ) {
        $where .= $wpdb->prepare(
            "AND (post_title LIKE '%s') ",
            $n . $search_terms[ $i ] . $n,
            $n . $search_terms[ $i ] . $n
        );
    }

    $where .= $wpdb->prepare(
        "OR (post_title LIKE '%s') ",
        $n . $search_string[0] . $n,
        $n . $search_string[0] . $n
    );

    $where .= ' ) ';
    $where .= $post_status;
    $where .= $post_types;

    /**
     * Filter the WHERE clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $where  		The WHERE clause of the query
     * @param string   $search_string[0]	Search query
     */
    $where = apply_filters( __NAMESPACE__ . '\\posts_where', $where );

    // Set Up to exclude current post or posts
    if (is_array($post_id)) {
        $limits = " AND ID != '" . join( "', '", $post_id ) ."' ";
    } else {
        $limits = " AND ID != '" . $post_id ."' ";
    }

    /**
     * Filter the ID != '' clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $limits  		The ORDER BY clause of the query
     * @param string   $search_string[0]	Search query
     */
    $limits = apply_filters( __NAMESPACE__ . '\\duplicate_detector_post_limits', $limits );

    /**
     * Filter the GROUP BY clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $groupby  		The GROUP BY clause of the query
     * @param string   $search_string[0]	Search query
     */
    $groupby = apply_filters( __NAMESPACE__ . '\\duplicate_detector_posts_groupby', $groupby );

    // GROUP BY clause
    if ( ! empty( $groupby ) ) {
        $groupby = 'GROUP BY ' . $groupby;
    }


    // Set Up ORDER BY clause
    if ( $by_date ) {
        $orderby = ' post_date DESC ';
    } else {
        $orderby = '';
    }

    /**
     * Filter the ORDER BY clause of the query.
     *
     * @since	0.0.2
     *
     * @param string   $orderby  		The ORDER BY clause of the query
     * @param string   $search_string[0]	Search query
     */
    $orderby = apply_filters( __NAMESPACE__ . '\\duplicate_detector_posts_orderby', $orderby );


    // Assemble the query
    $sql = "SELECT $fields FROM $wpdb->posts $join WHERE 1=1 $where $limits $groupby $orderby";

    /**
     * Filter the SQL string used to query the database.
     *
     * @since	0.0.2
     *
     * @param	string	$sql			MySQL string
     * @param	array	$search_string	Search query as array
     * @param 	bool	$boolean_mode	Set BOOLEAN mode for FULLTEXT searching
     * @param	bool	$by_date		Sort by date?
     */
    return apply_filters( __NAMESPACE__ . '\\sql_prepare', $sql, $search_string, $post_id, $post_status, $post_types, $by_date );
}