<?php
/*
Plugin Name: Custom Posts Per Page
Plugin URI: http://www.jeremyfelt.com/wordpress/plugins/custom-posts-per-page/
Description: Shows a custom set number of posts depending on the page.
Version: 1.2.2
Author: Jeremy Felt
Author URI: http://www.jeremyfelt.com
Text Domain: custom-posts-per-page
Domain Path: /lang
License: GPL2
*/

/*  Copyright 2011 Jeremy Felt (email: jeremy.felt@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

register_activation_hook( __FILE__, 'cpppc_activate' );

if ( is_admin() ){
    /*	If we're on the admin screen, we'll want to make sure that
         the appropriate settings are showing up. If we add the modified
         query here, the posts view in the admin screen changes as well. */
    add_action( 'admin_menu', 'cpppc_add_settings' );
    add_action( 'admin_init', 'cpppc_register_settings' );
    add_action( 'admin_init', 'cpppc_add_languages' );
    /*  Also want to provide a good looking 'settings' link when the plugin
        is activated. */
    add_filter( 'plugin_action_links', 'cpppc_plugin_action_links', 10, 2 );
}else {
    /*	If we're on any page other than the admin screen, we'll add our
         request modification. */
    add_filter( 'request', 'cpppc_modify_query' );
}

function cpppc_add_languages(){
    $plugin_dir = basename( dirname( __FILE__ ) ) . '/lang';
    load_plugin_textdomain( 'custom-posts-per-page', false, $plugin_dir );
}

function cpppc_activate() {
    /*  When the plugin is first activated, we'll set some default values in
        an options array. We'll pull the default value from the current Reading setting
        for 'posts_per_page' so that nothing changes unexpectedly. */
    $default_count = get_option( 'posts_per_page' );
    $current_options = get_option( 'cpppc_options' );

    $default_options = array(   'front_page_count' => $default_count,
        'index_count' => $default_count,
        'category_count' => $default_count,
        'category_count_paged' => $default_count,
        'tag_count' => $default_count,
        'tag_count_paged' => $default_count,
        'author_count' => $default_count,
        'author_count_paged' => $default_count,
        'archive_count' => $default_count,
        'archive_count_paged' => $default_count,
        'search_count' => $default_count,
        'search_count_paged' => $default_count,
        'default_count' => $default_count,
        'default_count_paged' => $default_count );

    /*  Compare existing options with default options and assign accordingly. */
    $cpppc_options = wp_parse_args( $current_options, $default_options );

    /*  We'll also get all of the currently registered custom post types and give them a default
        value of 0 if one has not previously been set. Custom post types are a special breed and
        we don't necessarily want them to match the default posts_per_page value without a
        conscious decision by the user. */
    $all_post_types = get_post_types( array( '_builtin' => false ) );
    foreach ( $all_post_types as $p=>$k ){
        $cpppc_options[ $p . '_count' ] = isset( $cpppc_options[ $p . '_count' ] ) ? $cpppc_options[ $p . '_count' ] : 0;
        $cpppc_options[ $p . '_count_paged' ] = isset( $cpppc_options[ $p . '_count_paged' ] ) ? $cpppc_options[ $p . '_count_paged' ] : 0;
    }

    /*  Add or update the new options. */
    update_option( 'cpppc_options', $cpppc_options );
}


function cpppc_plugin_action_links( $links, $file ) {
    /*  Function gratefully taken (and barely modified) from Pippin Williamson's
        WPMods article: http://www.wpmods.com/adding-plugin-action-links/ */
    static $this_plugin;

    if ( ! $this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }

    // check to make sure we are on the correct plugin
    if ( $file == $this_plugin ) {
        $settings_path = '/wp-admin/options-general.php?page=post-count-settings';
        $settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . $settings_path . '">' . __( 'Settings', 'custom-posts-per-page' ) . '</a>';
        array_unshift( $links, $settings_link );  // add the link to the list
    }

    return $links;
}

function cpppc_add_settings() {
    /*	Add the sub-menu item under the Settings top-level menu. */
    add_options_page( __('Posts Per Page', 'custom-posts-per-page' ), __('Posts Per Page', 'custom-posts-per-page'), 'manage_options', 'post-count-settings', 'cpppc_view_settings' );
}

function cpppc_view_settings() {
    /*	Display the main settings view for Custom Posts Per Page. */
    echo '<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
			<h2>' . __( 'Custom Posts Per Page', 'custom-posts-per-page' ) . '</h2>
			<h3>' . __( 'Overview', 'custom-posts-per-page' ) . ':</h3>
			<p style="margin-left:12px;max-width:640px;">' . __( 'The settings below allow you to specify how many posts per
			page are displayed to readers depending on the which type of page is being viewed. Different values can
			be set for your front page, subsequent main pages (page 2, 3...), category pages, tag pages, author pages, archive pages, search pages
			and custom post type pages. In addition to these, a default value is available that can be set for any
			other pages not covered by this.', 'custom-posts-per-page' ) . '</p>';
    echo '<p style="margin-left:12px;max-width:640px;">' . __( 'The initial value used on activation was pulled from the Blog Pages show at most setting under Reading.', 'custom-posts-per-page' ) . '</p>';
    echo '<form method="post" action="options.php">';

    settings_fields( 'cpppc_options' );
    do_settings_sections( 'cpppc' ); // Display the main section of settings.
    do_settings_sections( 'cpppc_custom' ); // Display the section of settings that handles custom post types.

    echo '<p class="submit"><input type="submit" class="button-primary" value="';
    _e( 'Save Changes', 'custom-posts-per-page' );
    echo '" />
			</p>
			</form>
		</div>';
}

function cpppc_register_settings() {
    /*	Add the settings that we're going to be using for the plugin. */
    register_setting( 'cpppc_options', 'cpppc_options', 'cpppc_options_validate' );
    add_settings_section( 'cpppc_section_main', '', 'cpppc_section_text', 'cpppc' );
    add_settings_section( 'cpppc_section_custom', '', 'cpppc_section_custom_text', 'cpppc_custom' );
    //add_settings_field( 'cpppc_front_page_count', __( 'Front Page posts per page:', 'custom-posts-per-page' ) , 'cpppc_front_page_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_index_count', __( 'Main Index posts per page:', 'custom-posts-per-page' ), 'cpppc_index_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_category_count', __( 'Category posts per page:', 'custom-posts-per-page' ), 'cpppc_category_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_archive_count', __( 'Archive posts per page:', 'custom-posts-per-page' ), 'cpppc_archive_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_tag_count', __( 'Tag posts per page:', 'custom-posts-per-page' ), 'cpppc_tag_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_author_count', __( 'Author posts per page:', 'custom-posts-per-page' ), 'cpppc_author_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_search_count', __( 'Search posts per page:', 'custom-posts-per-page' ), 'cpppc_search_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_default_count', __( 'Default posts per page:', 'custom-posts-per-page' ), 'cpppc_default_count_text', 'cpppc', 'cpppc_section_main' );
    add_settings_field( 'cpppc_post_type_count', '', 'cpppc_post_type_count_text', 'cpppc_custom', 'cpppc_section_custom' );
}

function cpppc_options_validate( $input ) {
    /*	We aren't doing heavy validation yet, more like a passive aggressive failure.
         If you enter anything other than an integer, the value will be set to 0 by
         default and if a negative value is inputted, it will be corrected to positive. */

    /*  Apply absint() to each element in the input array */
    $input = array_map( "absint", $input );

    return $input;
}

function cpppc_section_text() {
    echo '<h3>' . __( 'Main Settings', 'custom-posts-per-page' ) . ':</h3>
		<p style="max-width:640px;margin-left:12px;">' . __( 'This section allows you to modify page view types that are
		associated with WordPress by default. When an option is set to 0, it will not modify any page requests for
		that view and will instead allow default values to pass through.', 'custom-posts-per-page' ) . '</p>';
    echo '<p style="max-width:460px;margin-left:12px;"><strong>Please Note:</strong> <em>For each setting, the box on the <strong>LEFT</strong> controls the the number of posts displayed on
	the first page of that view while the box on the <strong>RIGHT</strong> controls the number of posts seen on pages 2, 3, 4, etc... of that view.</em></p>';
}

function cpppc_section_custom_text() {
    echo '<h3>' . __( 'Custom Post Type Specific Settings', 'custom-posts-per-page' ) . ':</h3>
	<p style="max-width:640px;margin-left:12px;">' . __( 'This section contains a list of all of your registered custom post
	types. In order to not conflict with other plugins or themes, these are set to 0 by default. When an option is
	set to 0, it will not modify any page requests for that custom post type archive. For Custom Posts Per Page to
	control the number of posts to display, these will need to be changed.', 'custom-post-per-page' ) . '</p>';
}

function cpppc_post_type_count_text() {
    $cpppc_options = get_option( 'cpppc_options' );
    $all_post_types = get_post_types( array( '_builtin' => false ) );

    echo '</td><td></td></tr>';

    foreach( $all_post_types as $p=>$k ) {
        /*	Default values are assigned for custom post types that are available
              to us when our plugin is registered. If a custom post type becomes
              available after our plugin is installed, we'll want to catch it and
              assign a good value. */
        if ( ! isset( $cpppc_options[ $p . '_count' ] ) ){
            $cpppc_options[ $p . '_count' ] = 0;
        }

        $this_post_data = get_post_type_object($p);

        echo '<tr><td>';
        echo $this_post_data->labels->name . '</td><td> <input id="cpppc_post_type_count[' . $p . ']" name="cpppc_options[' . $p . '_count]" size="10" type="text" value="';
        echo $cpppc_options[ $p . '_count' ];
        echo '">';
        echo '&nbsp;<input id="cpppc_post_type_count[' . $p . ']" name="cpppc_options[' . $p . '_count_paged]" size="10" type="text" value="';
        echo $cpppc_options[ $p . '_count_paged' ];
        echo '"></td></tr>';
    }
}

function cpppc_front_page_count_text() {
    /*  Display the input field for the front page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cpppc_front_page_count" name="cpppc_options[front_page_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'front_page_count' ];
    echo '" />';
}

function cpppc_index_count_text() {
    /*	Display the input field for the index page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cpppc_index_count[0]" name="cpppc_options[front_page_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'front_page_count' ];
    echo '" />';
    echo '&nbsp;<input id="cpppc_index_count[1]" name="cpppc_options[index_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'index_count' ];
    echo '" />';
}

function cpppc_category_count_text() {
    /*	Display the input field for the category page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cppppc_category_count[0]" name="cpppc_options[category_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'category_count' ];
    echo '" />';
    echo '&nbsp;<input id="cppppc_category_count[1]" name="cpppc_options[category_count_paged]" size="10" type="text" value="';
    echo $cpppc_options[ 'category_count_paged' ];
    echo '" />';
}

function cpppc_archive_count_text() {
    /*	Display the input field for the archive page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cppppc_archive_count[0]" name="cpppc_options[archive_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'archive_count' ];
    echo '" />';
    echo '&nbsp;<input id="cppppc_archive_count[1]" name="cpppc_options[archive_count_paged]" size="10" type="text" value="';
    echo $cpppc_options[ 'archive_count_paged' ];
    echo '" />';
}

function cpppc_tag_count_text() {
    /*	Display the input field for the tag page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cpppc_tag_count[0]" name="cpppc_options[tag_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'tag_count' ];
    echo '" />';
    echo '&nbsp;<input id="cpppc_tag_count[1]" name="cpppc_options[tag_count_paged]" size="10" type="text" value="';
    echo $cpppc_options[ 'tag_count_paged' ];
    echo '" />';
}

function cpppc_author_count_text() {
    /*	Display the input field for the author page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cpppc_author_count[0]" name="cpppc_options[author_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'author_count' ];
    echo '" />';
    echo '&nbsp;<input id="cpppc_author_count[1]" name="cpppc_options[author_count_paged]" size="10" type="text" value="';
    echo $cpppc_options[ 'author_count_paged' ];
    echo '" />';
}

function cpppc_search_count_text() {
    /*	Display the input field for the search page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cppppc_search_count[0]" name="cpppc_options[search_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'search_count' ];
    echo '" />';
    echo '&nbsp;<input id="cppppc_search_count[1]" name="cpppc_options[search_count_paged]" size="10" type="text" value="';
    echo $cpppc_options[ 'search_count_paged' ];
    echo '" />';
}

function cpppc_default_count_text() {
    /*	Display the input field for the default page post count option. */
    $cpppc_options = get_option( 'cpppc_options' );

    echo '<input id="cppppc_default_count[0]" name="cpppc_options[default_count]" size="10" type="text" value="';
    echo $cpppc_options[ 'default_count' ];
    echo '" />';
    echo '&nbsp;<input id="cppppc_default_count[1]" name="cpppc_options[default_count_paged]" size="10" type="text" value="';
    echo $cpppc_options[ 'default_count_paged' ];
    echo '" />';
}

function cpppc_modify_query( $request ) {
    /*	This is the important part of the plugin that actually modifies the query
         at the beginning of the page before anything is displayed. */
    $cpppc_options = get_option( 'cpppc_options' );
    $all_post_types = get_post_types( array( '_builtin' => false ) );
    $post_type_array = array();
    foreach ( $all_post_types as $p=>$k ) {
        $post_type_array[] = $p;
    }

    /*  Set our own page flag for our own sanity. */
    $cpppc_paged = ( isset( $request[ 'paged' ] ) ) ? 1 : NULL;

    $cpppc_query = new WP_Query();
    $cpppc_query->parse_query( $request );

    if( $cpppc_query->is_home() ){
        if ( ! $cpppc_paged && isset( $cpppc_options[ 'front_page_count' ] ) && 0 != $cpppc_options[ 'front_page_count' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'front_page_count' ];
        }elseif ( $cpppc_paged && isset( $cpppc_options[ 'index_count' ] ) && 0 != $cpppc_options[ 'index_count' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'index_count' ];
        }
    }elseif( $cpppc_query->is_post_type_archive( $post_type_array ) ) {
        /*	We've just established that the visitor is loading an archive
              page of a custom post type by matching it to a general array.
              Now we'll loop back through until we find exactly what post type
              is matching so we can modify the request accordingly. */
        foreach( $post_type_array as $my_post_type ) {
            if( $cpppc_query->is_post_type_archive( $my_post_type ) ) {
                /*	Now we know for sure what custom post type we're on. */
                $my_post_type_option = $my_post_type;
            }
        }
        /*	Now check to see if we've assigned a value to this yet. When our
              plugin is registered, only the custom post types available to us at
              the time are assigned options. If a new custom post type has been
              installed, it's possible it does not yet have an option. For now
              we'll skip the request modification and let it slide by if there is
              no match. */
        if( ! $cpppc_paged && 0 != $cpppc_options[ $my_post_type_option . '_count' ] && isset( $cpppc_options[ $my_post_type_option . '_count' ] ) ){
            $request[ 'posts_per_page' ] = $cpppc_options[ $my_post_type_option . '_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ $my_post_type_option . '_count_paged' ] && isset( $cpppc_options[ $my_post_type_option . '_count_paged' ] ) ){
            $request[ 'posts_per_page' ] = $cpppc_options[ $my_post_type_option . '_count_paged' ];
        }
    }elseif ( $cpppc_query->is_category() ) {
        if ( ! $cpppc_paged && 0 != $cpppc_options[ 'category_count' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'category_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ 'category_count_paged' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'category_count_paged' ];
        }
    }elseif ( $cpppc_query->is_tag() ) {
        if ( ! $cpppc_paged && 0 != $cpppc_options[ 'tag_count' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'tag_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ 'tag_count_paged' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'tag_count_paged' ];
        }
    }elseif ( $cpppc_query->is_author() ) {
        if ( ! $cpppc_paged && 0 != $cpppc_options[ 'author_count' ] ) {
            $request[ 'posts_per_page' ] = $cpppc_options[ 'author_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ 'author_count_paged' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'author_count_paged' ];
        }
    }elseif ( $cpppc_query->is_search() ) {
        if ( ! $cpppc_paged && 0 != $cpppc_options[ 'search_count' ] ) {
            $request[ 'posts_per_page' ] = $cpppc_options[ 'search_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ 'search_count_paged' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'search_count_paged' ];
        }
    }elseif ( $cpppc_query->is_archive() ) {
        /*	Note that the check for is_archive needs to be below anything else
              that WordPress may consider an archive. This includes is_tag, is_category, is_author
              and probably some others.
          */
        if ( ! $cpppc_paged && 0 != $cpppc_options[ 'archive_count' ] ) {
            $request[ 'posts_per_page' ] = $cpppc_options[ 'archive_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ 'archive_count_paged' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'archive_count_paged' ];
        }
    }else{
        if ( ! $cpppc_paged && 0 != $cpppc_options[ 'default_count' ] ) {
            $request[ 'posts_per_page' ] = $cpppc_options[ 'default_count' ];
        }elseif ( $cpppc_paged && 0 != $cpppc_options[ 'default_count_paged' ] ){
            $request[ 'posts_per_page' ] = $cpppc_options[ 'default_count_paged ' ];
        }
    }

    return $request;
}
?>