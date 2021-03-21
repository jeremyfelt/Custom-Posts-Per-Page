<?php

namespace CustomPostsPerPage\Main;

register_activation_hook( __FILE__, __NAMESPACE__ . '\upgrade_check' );
add_action( 'admin_init', __NAMESPACE__ . '\upgrade_check' );
add_filter( 'plugin_action_links', __NAMESPACE__ . '\add_plugin_action_links', 10, 2 );

/**
 * Provide a list of post types that should be available
 * for query modification.
 *
 * @return array A list of post type slugs.
 */
function get_supported_post_types() {
	$post_types = \get_post_types(
		array(
			'_builtin' => false,
		)
	);

	return array_keys( $post_types );
}

/**
 * Our database 'upgrade' check.
 *
 * In version 1.3, we refactored the option names a bit, so a little
 * cleanup is needed if we detect and old version.
 */
function upgrade_check() {
	if ( '1.3' === get_option( 'cpppc_upgrade', '1.3' ) ) {
		activate();

		$cpppc_options = get_option( 'cpppc_options' );

		if ( isset( $cpppc_options['front_page_count'] ) ) {
			$cpppc_options['front_count'] = $cpppc_options['front_page_count'];
			unset( $cpppc_options['front_page_count'] );
		}

		if ( isset( $cpppc_options['index_count'] ) ) {
			$cpppc_options['front_count_paged'] = $cpppc_options['index_count'];
			unset( $cpppc_options['index_count'] );
		}

		update_option( 'cpppc_options', $cpppc_options );
		update_option( 'cpppc_upgrade', '1.4' );
	}
}

/**
 * Activate the plugin when it is activated through the admin screen, or if it is upgraded
 * and we find that things are out of date in upgrade_check.
 *
 * When first activated, we set some default values in an options array. The default value
 * is pulled from the current 'posts_per_page' option so that nothing changes unexpectedly.
 */
function activate() {
	$default_count     = get_option( 'posts_per_page' );
	$current_options   = get_option( 'cpppc_options' );
	$default_options   = array();
	$option_type_array = array( 'front', 'category', 'tag', 'author', 'archive', 'search', 'default' );

	foreach ( $option_type_array as $option_type ) {
		$default_options[ $option_type . '_count' ] = absint( $default_count );

		/* For some users that are upgrading from a past version, we want to make sure the paged count
			* is filled in with something appropriate. This looks for each option in order. */
		if ( ! empty( $cppc_options[ $option_type . '_count_paged' ] ) ) {
			$default_options[ $option_type . '_count_paged' ] = absint( $current_options[ $option_type . '_count_paged' ] );
		} elseif ( ! empty( $cpppc_options[ $option_type . '_count' ] ) ) {
			$default_options[ $option_type . '_count_paged' ] = absint( $current_options[ $option_type . '_count' ] );
		} else {
			$default_options[ $option_type . '_count_paged' ] = absint( $default_count );
		}
	}

	/*  We'll also get all of the currently registered custom post types and give them a default value
		*  of 0 if one has not previously been set. Custom post types are a special breed and we don't
		*  necessarily want them to match the default posts_per_page value without a conscious decision
		*  by the user. */
	$all_post_types = get_supported_post_types();
	foreach ( $all_post_types as $post_type ) {
		if ( isset( $current_options[ $post_type . '_count' ] ) ) {
			$default_options[ $post_type . '_count' ] = absint( $current_options[ $post_type . '_count' ] );
		} else {
			$default_options[ $post_type . '_count' ] = 0;
		}

		if ( isset( $current_options[ $post_type . '_count_paged' ] ) ) {
			$default_options[ $post_type . '_count_paged' ] = absint( $current_options[ $post_type . '_count_paged' ] );
		} else {
			$default_options[ $post_type . '_count_paged' ] = 0;
		}
	}
	update_option( 'cpppc_options', $default_options );
}

/**
 * Adds a pretty 'settings' link under the plugin upon activation.
 *
 * @param $links array of links provided by core that will be displayed under the plugin
 * @param $file string representing the plugin's filename
 * @return array the new array of links to be displayed
 */
function add_plugin_action_links( $links, $file ) {
	if ( 'custom-posts-per-page/custom-posts-per-page.php' === $file ) {
		$settings_link = '<a href="' . site_url( '/wp-admin/options-general.php?page=post-count-settings' ) . '">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}
