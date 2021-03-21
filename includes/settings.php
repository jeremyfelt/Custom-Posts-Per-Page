<?php

namespace CustomPostsPerPage\Settings;

add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );
add_action( 'admin_menu', __NAMESPACE__ . '\add_settings' );

/**
 * Add the settings page for Posts Per Page under the settings menu.
 */
function add_settings() {
	add_options_page(
		__( 'Posts Per Page', 'custom-posts-per-page' ),
		__( 'Posts Per Page', 'custom-posts-per-page' ),
		'manage_options',
		'post-count-settings',
		__NAMESPACE__ . '\view_settings'
	);
}

/**
 * Display the main settings view for the plugin.
 */
function view_settings() {
	$default_string = sprintf(
		/* translators: 1: An anchor link to the Reading Settings page in WordPress. */
		__( 'The initial default value was pulled from the setting "Blog Pages show at most" found in the %s' ),
		'<a href="' . site_url( '/wp-admin/options-reading.php' ) . '">' . __( 'Reading Settings' ) . '</a>'
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Custom Posts Per Page', 'custom-posts-per-page' ); ?></h1>
		<h2><?php esc_html_e( 'Overview', 'custom-posts-per-page' ); ?></h2>
		<p><?php esc_html_e( 'The settings below allow you to specify how many posts per page are displayed to readers depending on the which type of page is being viewed.' ); ?></p>
		<p><?php echo wp_kses_post( $default_string ); ?></p>

		<p><?php esc_html_e( 'Each box on the LEFT controls the the posts per page count on the first page of that view. Each box on the right controls the count on subsequent pages of that view.', 'custom-posts-per-page' ); ?></p>
		<p><?php esc_html_e( 'When an option is set to 0, the plugin will not modify any page requests for that view and will instead allow default values to pass through.', 'custom-posts-per-page' ); ?></p>

		<form method="post" action="options.php">
			<?php
				settings_fields( 'cpppc_options' );
				do_settings_sections( 'cpppc' );
				do_settings_sections( 'cpppc_custom' );
			?>
			<p class="submit"><input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes' ); ?>" /></p>
		</form>
	</div>
	<?php
}

/**
 * Register all of the settings we'll be using.
 */
function register_settings() {
	register_setting( 'cpppc_options', 'cpppc_options', __NAMESPACE__ . '\validate_options' );

	add_settings_section( 'cpppc_section_main', '', __NAMESPACE__ . '\output_main_section_text', 'cpppc' );
	add_settings_section( 'cpppc_section_custom', '', __NAMESPACE__ . '\output_custom_section_text', 'cpppc_custom' );

	add_settings_field( 'cpppc_index_count', __( 'Main Index posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_index_count_text', 'cpppc', 'cpppc_section_main' );
	add_settings_field( 'cpppc_category_count', __( 'Category posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_category_count_text', 'cpppc', 'cpppc_section_main' );
	add_settings_field( 'cpppc_archive_count', __( 'Archive posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_archive_count_text', 'cpppc', 'cpppc_section_main' );
	add_settings_field( 'cpppc_tag_count', __( 'Tag posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_tag_count_text', 'cpppc', 'cpppc_section_main' );
	add_settings_field( 'cpppc_author_count', __( 'Author posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_author_count_text', 'cpppc', 'cpppc_section_main' );
	add_settings_field( 'cpppc_search_count', __( 'Search posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_search_count_text', 'cpppc', 'cpppc_section_main' );
	add_settings_field( 'cpppc_default_count', __( 'Default posts per page:', 'custom-posts-per-page' ), __NAMESPACE__ . '\output_default_count_text', 'cpppc', 'cpppc_section_main' );
}

/**
 * Validate the values entered by the user.
 *
 * We aren't doing heavy validation yet, more like a passive aggressive failure.
 * If you enter anything other than an integer, the value will be set to 0 by
 * default and if a negative value is inputted, it will be corrected to positive.
 *
 * @param $input array of counts destined to be used as posts_per_page options
 * @return array the same array with absint run on each
 */
function validate_options( $input ) {
	return array_map( 'absint', $input );
}

/**
 * Output the main section of text.
 */
function output_main_section_text() {
	?>
	<h2><?php esc_html_e( 'Main Settings', 'custom-posts-per-page' ); ?></h2>
	<p><?php esc_html_e( 'This section allows you to modify page view types associated with WordPress by default.', 'custom-posts-per-page' ); ?></p>
	<?php
}

/**
 * Output the custom post type section of text.
 */
function output_custom_section_text() {
	?>
	<h2><?php esc_html_e( 'Custom Post Type Specific Settings', 'custom-posts-per-page' ); ?></h2>
	<p><?php esc_html_e( 'This section contains a list of all of your publicly registered custom post types.', 'custom-post-per-page' ); ?></p>
	<?php

	output_post_type_count_text();
}

/**
 * Output the individual options for each custom post type registered in WordPress
 */
function output_post_type_count_text() {
	$cpppc_options  = get_option( 'cpppc_options' );
	$all_post_types = \CustomPostsPerPage\Main\get_supported_post_types();

	/* Quirky little workaround for displaying the settings in our table */
	echo '<table class="form-table" role="presentation"><tbody>';

	foreach ( $all_post_types as $post_type ) {
		/*	Default values are assigned for custom post types that are available
			*  to us when our plugin is registered. If a custom post type becomes
			*  available after our plugin is installed, we'll want to catch it and
			*  assign a good value. */
		if ( empty( $cpppc_options[ $post_type . '_count' ] ) ) {
			$cpppc_options[ $post_type . '_count' ] = 0;
		}

		if ( empty( $cpppc_options[ $post_type . '_count_paged' ] ) ) {
			$cpppc_options[ $post_type . '_count_paged' ] = 0;
		}

		$this_post_data = get_post_type_object( $post_type );

		/* translators: 1: custom post type archive name. */
		$single_label = sprintf( __( 'Posts per page value for first page of the %s archive view.', 'custom-posts-per-page' ), $this_post_data->labels->name );

		/* translators: 1: custom post type archive name. */
		$more_label = sprintf( __( 'Posts per page value for subsequent pages of the %s archive view.', 'custom-posts-per-page' ), $this_post_data->labels->name );
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $this_post_data->labels->name ); ?></th>
			<td>
				<label for="cpppc_post_type_count[<?php echo esc_attr( $post_type ); ?>]" class="screen-reader-text" ><?php echo esc_html( $single_label ); ?></label>
				<input id="cpppc_post_type_count[<?php echo esc_attr( $post_type ); ?>]" name="cpppc_options[<?php echo esc_attr( $post_type ); ?>_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ $post_type . '_count' ] ); ?>" />
				<label for="cpppc_post_type_count[<?php echo esc_attr( $post_type ); ?>_count_paged]" class="screen-reader-text" ><?php echo esc_html( $more_label ); ?></label>
				<input id="cpppc_post_type_count[<?php echo esc_attr( $post_type ); ?>_count_paged]" name="cpppc_options[<?php echo esc_attr( $post_type ); ?>_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options[ $post_type . '_count_paged' ] ); ?>" />
			</td>
		</tr>
		<?php
	}

	echo '</tbody></table>';
}

/**
 * Display the input field for the index page post count option.
 */
function output_index_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'front_count'       => 0,
			'front_count_paged' => 0,
		)
	);

	?>
	<label for="cpppc_index_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for first page of the main index view.', 'custom-posts-per-page' ); ?></label>
	<input id="cpppc_index_count[0]" name="cpppc_options[front_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['front_count'] ); ?>" />
	<label for="cpppc_index_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for subsequent pages of the main index view.', 'custom-posts-per-page' ); ?></label>
	<input id="cpppc_index_count[1]" name="cpppc_options[front_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['front_count_paged'] ); ?>" />
	<?php
}

/**
 * Display the input field for the category page post count option.
 */
function output_category_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'category_count'       => 0,
			'category_count_paged' => 0,
		)
	);

	?>
	<label for="cppppc_category_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for first page of the category archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_category_count[0]" name="cpppc_options[category_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['category_count'] ); ?>" />
	<label for="cppppc_category_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for subsequent pages of the category archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_category_count[1]" name="cpppc_options[category_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['category_count_paged'] ); ?>" />
	<?php
}

/**
 * Display the input field for the archive page post count option.
 */
function output_archive_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'archive_count'       => 0,
			'archive_count_paged' => 0,
		)
	);

	?>
	<label for="cppppc_archive_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for first page of a general archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_archive_count[0]" name="cpppc_options[archive_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['archive_count'] ); ?>" />
	<label for="cppppc_archive_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for subsequent pages of a general archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_archive_count[1]" name="cpppc_options[archive_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['archive_count_paged'] ); ?>" />
	<?php
}

/**
 * Display the input field for the tag page post count option.
 */
function output_tag_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'tag_count'       => 0,
			'tag_count_paged' => 0,
		)
	);

	?>
	<label for="cpppc_tag_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for first page of the tag archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cpppc_tag_count[0]" name="cpppc_options[tag_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['tag_count'] ); ?>" />
	<label for="cpppc_tag_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for subsequent pages of the tag archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cpppc_tag_count[1]" name="cpppc_options[tag_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['tag_count_paged'] ); ?>" />
	<?php
}

/**
 * Display the input field for the author page post count option.
 */
function output_author_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'author_count'       => 0,
			'author_count_paged' => 0,
		)
	);

	?>
	<label for="cpppc_author_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for first page of the author archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cpppc_author_count[0]" name="cpppc_options[author_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['author_count'] ); ?>" />
	<label for="cpppc_author_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for subsequent pages of the author archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cpppc_author_count[1]" name="cpppc_options[author_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['author_count_paged'] ); ?>" />
	<?php
}

/**
 * Display the input field for the search page post count option.
 */
function output_search_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'search_count'       => 0,
			'search_count_paged' => 0,
		)
	);

	?>
	<label for="cppppc_search_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for first page of the search archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_search_count[0]" name="cpppc_options[search_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['search_count'] ); ?>" />
	<label for="cppppc_search_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Posts per page value for subsequent pages of the search archive view.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_search_count[1]" name="cpppc_options[search_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['search_count_paged'] ); ?>" />
	<?php
}

/**
 * Display the input field for the default page post count option.
 */
function output_default_count_text() {
	$cpppc_options = get_option(
		'cpppc_options',
		array(
			'default_count'       => 0,
			'default_count_paged' => 0,
		)
	);

	?>
	<label for="cppppc_default_count[0]" class="screen-reader-text"><?php echo esc_html_e( 'Default posts per page override value for the first page of archive views.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_default_count[0]" name="cpppc_options[default_count]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['default_count'] ); ?>" />
	<label for="cppppc_default_count[1]" class="screen-reader-text"><?php echo esc_html_e( 'Default posts per page override value for subsequent pages of archive views.', 'custom-posts-per-page' ); ?></label>
	<input id="cppppc_default_count[1]" name="cpppc_options[default_count_paged]" size="10" type="text" value="<?php echo esc_attr( $cpppc_options['default_count_paged'] ); ?>" />
	<?php
}
