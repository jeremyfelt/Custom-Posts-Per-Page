<?php

namespace CustomPostsPerPage\Query;

add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_query' );

/**
 * We use this function to abstract the processing of options while we determine what
 * type of view we're working with and what to use for the count on the initial page
 * and subsequent pages. The options are stored in a private property that allows us
 * access throughout the class after this.
 *
 * @param \WP_Query $query The current query object.
 * @return array Options to use for query modification.
 */
function get_options( $query ) {
	$cpppc_options = get_option( 'cpppc_options' );
	$options       = array();

	$view       = get_view( $query );
	$paged_view = ( $query->get( 'paged' ) && 2 <= $query->get( 'paged' ) ) ? true : false;

	if ( ! $paged_view && ! empty( $cpppc_options[ $view . '_count' ] ) ) {
		$options['posts']           = $cpppc_options[ $view . '_count' ];
		$options['offset']          = 0;
		$options['set_count']       = $cpppc_options[ $view . '_count' ];
		$options['set_count_paged'] = $cpppc_options[ $view . '_count_paged' ];
	} elseif ( $paged_view & ! empty( $cpppc_options[ $view . '_count_paged' ] ) ) {
		// I'm still setting this, but it's unused. Confused!
		$page_count_offset          = ( $cpppc_options[ $view . '_count_paged' ] - $cpppc_options[ $view . '_count' ] );
		$options['offset']          = ( ( $query->get( 'paged' ) - 2 ) * $cpppc_options[ $view . '_count_paged' ] + $cpppc_options[ $view . '_count' ] );
		$options['posts']           = $cpppc_options[ $view . '_count_paged' ];
		$options['set_count']       = $cpppc_options[ $view . '_count' ];
		$options['set_count_paged'] = $cpppc_options[ $view . '_count_paged' ];
	}

	return $options;
}

/**
 * Return a tag matching the current view.
 *
 * @param \WP_Query $query The current query object.
 * @return string The value used to represent a view.
 */
function get_view( $query ) {
	if ( $query->is_home() ) {
		return 'front';
	} elseif ( $query->is_post_type_archive( \CustomPostsPerPage\Main\get_supported_post_types() ) ) {
		$current_post_type_object = $query->get_queried_object();
		return $current_post_type_object->name;
	} elseif ( $query->is_category() ) {
		return 'category';
	} elseif ( $query->is_tag() ) {
		return 'tag';
	} elseif ( $query->is_author() ) {
		return 'author';
	} elseif ( $query->is_search() ) {
		return 'search';
	} elseif ( $query->is_archive() ) {
		// Note the check for is_archive is included after more specific archive checks so
		// that it does not return early.
		return 'archive';
	}

	return 'default';
}

/**
 * This is the important part of the plugin that actually modifies the query before anything
 * is displayed.
 *
 * @param $query WP Query object
 * @return mixed
 */
function modify_query( $query ) {

	/*  If this isn't the main query, we'll avoid altering the results. */
	if ( ! $query->is_main_query() || is_admin() ) {
		return;
	}

	$options = get_options( $query );

	if ( isset( $options['posts'] ) ) {
		$query->set( 'posts_per_page', absint( $options['posts'] ) );
		$query->set( 'offset', absint( $options['offset'] ) );
	}

	add_filter( 'found_posts', __NAMESPACE__ . '\correct_found_posts', 10, 2 );
}

/**
 * The offset and post count deal gets a bit confused when the first page and subsequent pages stop matching.
 * This function helps realign things once we've screwed with them by doing some math to determine how many
 * posts we need to return to the query in order for core to calculate the correct number of pages required.
 *
 * It should be noted here that found_posts is modified if the value of posts per page is different for page 1
 * than subsequent pages. This is intended to resolve pagination issues in popular WordPress plugins, but can
 * possibly cause related issues for other things that are depending on an exact found posts value.
 *
 * @param int       $found_posts The number of found posts
 * @param \WP_Query $query       The current query object.
 * @return int The number of posts to report as found.
 */
function correct_found_posts( $found_posts, $query ) {
	$options = get_options( $query );

	if ( empty( $options['set_count'] ) || empty( $options['set_count_paged'] ) ) {
		return $found_posts;
	}

	// We don't have the same issues if our first page and paged counts are the same as the math is easy then
	if ( $options['set_count'] === $options['set_count_paged'] ) {
		return $found_posts;
	}

	// Do the true calculation for pages required based on both
	// values: page 1 posts count and subsequent page post counts
	$pages_required = ( ( ( $found_posts - $options['set_count'] ) / $options['set_count_paged'] ) + 1 );

	if ( 0 === $query->get( 'paged' ) ) {
		return $pages_required * $options['set_count'];
	}

	if ( 1 < $query->get( 'paged' ) ) {
		return $pages_required * $options['set_count_paged'];
	}

	return $found_posts;
}
