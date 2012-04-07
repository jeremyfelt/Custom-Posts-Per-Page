<?php
/*
Plugin Name: Custom Posts Per Page
Plugin URI: http://www.jeremyfelt.com/wordpress/plugins/custom-posts-per-page/
Description: Shows a custom set number of posts depending on the type of page being viewed.
Version: 1.4
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

class Custom_Posts_Per_Page_Foghlaim {

	private $page_count_offset = 0;

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'upgrade_check' ) );
		add_action( 'admin_init', array( $this, 'upgrade_check' ) );
		add_action( 'admin_menu', array( $this, 'add_settings' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'add_languages' ) );
		add_filter( 'plugin_actions_links', array( $this, 'add_plugin_action_links' ), 10, 2 );
		if ( ! is_admin() )
			add_action( 'pre_get_posts', array( $this, 'modify_query' ) );
	}

	public function add_languages() {
		$plugin_dir = basename( dirname(__FILE__) ) . '/lang';
		load_plugin_textdomain( 'custom-posts-per-page', false, $plugin_dir );
	}

	public function upgrade_check() {
		if ( '1.3' == get_option( 'cpppc_upgrade', '1.3' ) ) {
			$this->activate();

			$cpppc_options = get_option( 'cpppc_options' );

			if ( isset( $cpppc_options[ 'front_page_count' ] ) ) {
				$cpppc_options[ 'front_count' ] = $cpppc_options[ 'front_page_count' ];
				unset( $cpppc_options[ 'front_page_count' ] );
			}

			if ( isset( $cpppc_options[ 'index_count' ] ) ) {
				$cpppc_options[ 'front_count_paged' ] = $cpppc_options[ 'index_count' ];
				unset( $cpppc_options[ 'index_count' ] );
			}

			update_option( 'cpppc_options', $cpppc_options );
			update_option( 'cpppc_upgrade', '1.4' );
		}
	}

	public function activate() {
		/* When the plugin is first activated, we'll set some default values in an options
		 * array. We'll pull the default value from the current Reading setting	for
		 * 'posts_per_page' so that nothing changes unexpectedly. */
		$default_count     = get_option( 'posts_per_page' );
		$current_options   = get_option( 'cpppc_options' );
		$default_options   = array();
		$option_type_array = array( 'front', 'category', 'tag', 'author', 'archive', 'search', 'default' );

		foreach ( $option_type_array as $option_type ) {
			$default_options[ $option_type . '_count' ] = $default_count;
			/*  If the user has already set an option for one of the existing views, we don't want the
			 *  paged views to act differently all of a sudden. We'll match those existing values before
			 *  going with the default. */
			if ( isset( $cpppc_options[ $option_type . '_count' ] ) )
				$default_options[ $option_type . '_count_paged' ] = $current_options[ $option_type . '_count' ];
			else
				$default_options[ $option_type . '_count_paged' ] = $default_count;
		}

		/*  We'll also get all of the currently registered custom post types and give them a default value
		 *  of 0 if one has not previously been set. Custom post types are a special breed and we don't
		 *  necessarily want them to match the default posts_per_page value without a conscious decision
		 *  by the user. */
		$all_post_types = get_post_types( array( '_builtin' => false ) );
		foreach ( $all_post_types as $p => $k ) {
			if ( isset( $current_options[ $p . '_count' ] ) )
				$default_options[ $p . '_count' ] = $current_options[ $p . '_count' ];
			else
				$default_options[ $p . '_count' ] = 0;

			if ( isset( $current_options[ $p . '_count_paged' ] ) )
				$default_options[ $p . '_count_paged' ] = $current_options[ $p . '_count_paged' ];
			else
				$default_options[ $p . '_count_paged' ] = 0;
		}
		update_option( 'cpppc_options', $default_options );
	}

	public function add_plugin_action_links( $links, $file ) {
		/*  Function gratefully taken (and barely modified) from Pippin Williamson's
		 *  WPMods article: http://www.wpmods.com/adding-plugin-action-links/ */
		static $this_plugin;

		if ( ! $this_plugin )
			$this_plugin = plugin_basename( __FILE__ );

		/*  Make sure we are on the correct plugin */
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . site_url( '/wp-admin/options-general.php?page=post-count-settings' ) . '">' . __('Settings', 'custom-posts-per-page') . '</a>';
			array_unshift( $links, $settings_link ); // add the link to the list
		}
		return $links;
	}

	public function add_settings() {
		add_options_page( __( 'Posts Per Page', 'custom-posts-per-page' ), __( 'Posts Per Page', 'custom-posts-per-page' ), 'manage_options', 'post-count-settings', array( $this, 'view_settings' ) );
	}

	public function view_settings() {
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>
			<h2><?php _e( 'Custom Posts Per Page', 'custom-posts-per-page' ); ?></h2>
			<h3><?php _e( 'Overview', 'custom-posts-per-page' ); ?></h3>
			<p style="margin-left:12px;max-width:640px;"><?php _e( 'The settings below allow you to specify how many posts per page are displayed to readers depending on the which type of page is being viewed.' ); ?></p>
			<p style="margin-left:12px;max-width:640px;"><?php _e( 'Different values can be set for your your main view, category views, tag views, author views, archive views, search views, and
			views for custom post types. For each of these views, a different setting is available for the first page and subsequent pages. In addition to these, a default value is available that
			can be set for any other pages not covered by this.', 'custom-posts-per-page' ); ?></p>
			<p style="margin-left:12px;max-width:640px;"><?php _e( 'The initial value used on activation was pulled from the setting <em>Blog Pages show at most</em> found in the', 'custom-posts-per-page' ); ?> <a href="<?php echo site_url( '/wp-admin/options-reading.php' ); ?>" title="Reading Settings"><?php _e( 'Reading Settings', 'custom-posts-per-page' ); ?></a></p>
			<form method="post" action="options.php">
	<?php
		settings_fields( 'cpppc_options' );
		do_settings_sections( 'cpppc' ); // Display the main section of settings.
		do_settings_sections( 'cpppc_custom' ); // Display the section of settings that handles custom post types.
	?>
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'custom-posts-per-page' ); ?>" /></p></form>
		</div>
	<?php
	}

	public function register_settings() {
		register_setting( 'cpppc_options', 'cpppc_options', array( $this, 'validate_options' ) );
		add_settings_section( 'cpppc_section_main', '', array( $this, 'output_main_section_text' ), 'cpppc' );
		add_settings_section( 'cpppc_section_custom', '', array( $this, 'output_custom_section_text' ), 'cpppc_custom' );
		add_settings_field( 'cpppc_index_count', __( 'Main Index posts per page:', 'custom-posts-per-page' ), array( $this, 'output_index_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_category_count', __( 'Category posts per page:', 'custom-posts-per-page' ), array( $this, 'output_category_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_archive_count', __( 'Archive posts per page:', 'custom-posts-per-page' ), array( $this, 'output_archive_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_tag_count', __( 'Tag posts per page:', 'custom-posts-per-page' ), array( $this, 'output_tag_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_author_count', __( 'Author posts per page:', 'custom-posts-per-page' ), array( $this, 'output_author_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_search_count', __( 'Search posts per page:', 'custom-posts-per-page' ), array( $this, 'output_search_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_default_count', __( 'Default posts per page:', 'custom-posts-per-page' ), array( $this, 'output_default_count_text' ), 'cpppc', 'cpppc_section_main' );
		add_settings_field( 'cpppc_post_type_count', '', array( $this, 'output_post_type_count_text' ), 'cpppc_custom', 'cpppc_section_custom' );
	}

	public function validate_options( $input ) {
		/*	We aren't doing heavy validation yet, more like a passive aggressive failure.
		 *  If you enter anything other than an integer, the value will be set to 0 by
		 *  default and if a negative value is inputted, it will be corrected to positive. */
		return array_map( 'absint', $input );
	}

	public function output_main_section_text() {
	?>
		<h3><?php _e( 'Main Settings', 'custom-posts-per-page' ); ?></h3>
		<p style="max-width:640px;margin-left:12px;"><?php _e( 'This section allows you to modify page view types that are
		associated with WordPress by default. When an option is set to 0, it will not modify any page requests for
		that view and will instead allow default values to pass through.', 'custom-posts-per-page' ); ?></p>
		<p style="max-width:460px;margin-left:12px;"><strong><?php _e( 'Please Note', 'custom-posts-per-page' ); ?>:</strong>
		<em><?php _e( 'For each setting, the box on the <strong>LEFT</strong> controls the the number of posts displayed on	the first page of that view while
		the box on the <strong>RIGHT</strong> controls the number of posts seen on pages 2, 3, 4, etc... of that view.', 'custom-posts-per-page' ); ?></em></p>
	<?php
	}

	public function output_custom_section_text() {
	?>
	<h3><?php _e( 'Custom Post Type Specific Settings', 'custom-posts-per-page' ); ?></h3>
	<p style="max-width:640px;margin-left:12px;"><?php _e( 'This section contains a list of all of your registered custom post
	types. In order to not conflict with other plugins or themes, these are set to 0 by default. When an option is
	set to 0, it will not modify any page requests for that custom post type archive. For Custom Posts Per Page to
	control the number of posts to display, these will need to be changed.', 'custom-post-per-page' ); ?></p>
	<?php
	}

	public function output_post_type_count_text() {
		$cpppc_options  = get_option( 'cpppc_options' );
		$all_post_types = get_post_types( array( '_builtin' => false ) );

		/* Quirky little workaround for displaying the settings in our table */
		echo '</td><td></td></tr>';

		foreach ( $all_post_types as $p=> $k ) {
			/*	Default values are assigned for custom post types that are available
			 *  to us when our plugin is registered. If a custom post type becomes
			 *  available after our plugin is installed, we'll want to catch it and
			 *  assign a good value. */
			if ( ! isset( $cpppc_options[ $p . '_count' ] ) )
				$cpppc_options[ $p . '_count' ] = 0;

			$this_post_data = get_post_type_object( $p );

		?>
			<tr>
				<td><?php echo $this_post_data->labels->name; ?></td>
				<td><input id="cpppc_post_type_count['<?php echo $p; ?>']" name="cpppc_options['<?php echo $p; ?>_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ $p . '_count' ] ); ?>" />
					&nbsp;<input id="cpppc_post_type_count['<?php echo $p; ?>']" name="cpppc_options['<?php echo $p; ?>_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ $p . '_count_paged' ] ); ?>" />
				</td>
			</tr>
		<?php
		}
	}

	public function output_index_count_text() {
		/*	Display the input field for the index page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'front_count' => 0, 'front_count_paged' => 0 ) );
	?>
		<input id="cpppc_index_count[0]" name="cpppc_options[front_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'front_count' ] ); ?>" />
		&nbsp;<input id="cpppc_index_count[1]" name="cpppc_options[front_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'front_count_paged' ] ); ?>" />
	<?php
	}

	public function output_category_count_text() {
		/*	Display the input field for the category page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'category_count' => 0, 'category_count_paged' => 0 ) );
	?>
		<input id="cppppc_category_count[0]" name="cpppc_options[category_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'category_count' ] ); ?>" />
		&nbsp;<input id="cppppc_category_count[1]" name="cpppc_options[category_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'category_count_paged' ] ); ?>" />
	<?php
	}

	public function output_archive_count_text() {
		/*	Display the input field for the archive page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'archive_count' => 0, 'archive_count_paged' => 0 ) );
	?>
		<input id="cppppc_archive_count[0]" name="cpppc_options[archive_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'archive_count' ] ); ?>" />
		&nbsp;<input id="cppppc_archive_count[1]" name="cpppc_options[archive_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'archive_count_paged' ] ); ?>" />
	<?php
	}

	public function output_tag_count_text() {
		/*	Display the input field for the tag page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'tag_count' => 0, 'tag_count_paged' => 0 ) );
	?>
		<input id="cpppc_tag_count[0]" name="cpppc_options[tag_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'tag_count' ] ); ?>" />
		&nbsp;<input id="cpppc_tag_count[1]" name="cpppc_options[tag_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'tag_count_paged' ] ); ?>" />
	<?php
	}

	public function output_author_count_text() {
		/*	Display the input field for the author page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'author_count' => 0, 'author_count_paged' => 0 ) );
	?>
		<input id="cpppc_author_count[0]" name="cpppc_options[author_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'author_count' ] ); ?>" />
		&nbsp;<input id="cpppc_author_count[1]" name="cpppc_options[author_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'author_count_paged' ] ); ?>" />
	<?php
	}

	public function output_search_count_text() {
		/*	Display the input field for the search page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'search_count' => 0, 'search_count_paged' => 0 ) );
	?>
		<input id="cppppc_search_count[0]" name="cpppc_options[search_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'search_count' ] ); ?>" />
		&nbsp;<input id="cppppc_search_count[1]" name="cpppc_options[search_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'search_count_paged' ] ); ?>" />
	<?php
	}

	public function output_default_count_text() {
		/*	Display the input field for the default page post count option. */
		$cpppc_options = get_option( 'cpppc_options', array( 'default_count' => 0, 'default_count_paged' => 0 ) );
	?>
		<input id="cppppc_default_count[0]" name="cpppc_options[default_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'default_count' ] ); ?>" />
		&nbsp;<input id="cppppc_default_count[1]" name="cpppc_options[default_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ 'default_count_paged' ] ); ?>" />
	<?php
	}

	public function check_main_query( $query ) {
		if ( method_exists( $query, 'is_main_query' ) ) {
			return $query->is_main_query();
		} else {
			global $wp_the_query;
			return $query === $wp_the_query;
		}
	}

	public function modify_query( $query ) {

		/*  If this isn't the main query, we'll avoid altering the results. */
		if ( ! $this->check_main_query( $query ) )
			return;

		/*  This is the important part of the plugin that actually modifies the
		 *  query at the beginning of the page before anything is displayed. */
		$cpppc_options   = get_option( 'cpppc_options' );
		$all_post_types  = get_post_types( array( '_builtin' => false ) );
		$post_type_array = array();
		foreach ( $all_post_types as $p=> $k ) {
			$post_type_array[] = $p;
		}

		/*  Set our own page flag for our own sanity. */
		$cpppc_paged = ( $query->get( 'paged' ) && 2 <= $query->get( 'paged' ) ) ? 1 : NULL;
		$page_number = $query->get( 'paged' );

		if ( $query->is_home() ) {
			$final_options = $this->process_options( 'front', $cpppc_paged, $cpppc_options, $page_number );
		} elseif ( $query->is_post_type_archive( $post_type_array ) ) {
			/*	We've just established that the visitor is loading an archive page of a custom post type by matching
			 *  it to a general array. Now we'll loop back through until we find exactly what post type is matching
			 *  so we can modify the request accordingly. */
			foreach ( $post_type_array as $my_post_type ) {
				if ( $query->is_post_type_archive( $my_post_type ) ) {
					/*	Now we know for sure what custom post type we're on. */
					$my_post_type_option = $my_post_type;
				}
			}
			/*	Now check to see if we've assigned a value to this yet. When our plugin is registered, only the custom
			 *  post types available to us at the time are assigned options. If a new custom post type has been
			 *  installed, it's possible it does not yet have an option. For now we'll skip the request modification
			 *  and let it slide by if there is no match. */
			$final_options = $this->process_options( $my_post_type_option, $cpppc_paged, $cpppc_options, $page_number );
		} elseif ( $query->is_category() ) {
			$final_options = $this->process_options( 'category', $cpppc_paged, $cpppc_options, $page_number );
		} elseif ( $query->is_tag() ) {
			$final_options = $this->process_options( 'tag', $cpppc_paged, $cpppc_options, $page_number );
		} elseif ( $query->is_author() ) {
			$final_options = $this->process_options( 'author', $cpppc_paged, $cpppc_options, $page_number );
		} elseif ( $query->is_search() ) {
			$final_options = $this->process_options( 'search', $cpppc_paged, $cpppc_options, $page_number );
		} elseif ( $query->is_archive() ) {
			/*  Note that the check for is_archive needs to be below anything else that WordPress may consider an
			 *  archive. This includes is_tag, is_category, is_author and probably some others.	*/
			$final_options = $this->process_options( 'archive', $cpppc_paged, $cpppc_options, $page_number );
		} else {
			$final_options = $this->process_options( 'default', $cpppc_paged, $cpppc_options, $page_number );
		}

		if ( isset( $final_options[ 'posts' ] ) ) {
			$query->set( 'posts_per_page', $final_options[ 'posts' ] );
			$query->set( 'offset', $final_options[ 'offset' ] );
		}

		if ( 0 <> $this->page_count_offset )
			add_filter( 'found_posts', array( $this, 'correct_found_posts' ) );

	}

	public function correct_found_posts( $found_posts ) {
		return ( $found_posts + $this->page_count_offset );
	}

	public function process_options( $option_prefix, $cpppc_paged, $cpppc_options, $page_number = NULL ) {
		$final_options = array();

		if ( ! $cpppc_paged && 0 != $cpppc_options[ $option_prefix . '_count' ] ) {
			$final_options[ 'posts' ]  = $cpppc_options[ $option_prefix . '_count' ];
			$final_options[ 'offset' ] = 0;
		} elseif ( $cpppc_paged & 0 != $cpppc_options[ $option_prefix . '_count_paged' ] ) {
			$this->page_count_offset = ( $cpppc_options[ $option_prefix . '_count_paged' ] - $cpppc_options[ $option_prefix . '_count' ] );
			$final_options[ 'offset' ]  = ( ( $page_number - 2 ) * $cpppc_options[ $option_prefix . '_count_paged' ] + $cpppc_options[ $option_prefix . '_count' ] );
			$final_options[ 'posts' ]   = $cpppc_options[ $option_prefix . '_count_paged' ];
		}
		return $final_options;
	}
}
new Custom_Posts_Per_Page_Foghlaim();