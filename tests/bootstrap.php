<?php
/**
 * PHPUnit bootstrap file.
 *
 * @see https://github.com/wp-phpunit/example-plugin/blob/master/tests/bootstrap.php
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function() {
	// test set up, plugin activation, etc.
	require dirname( __DIR__ ) . '/custom-posts-per-page.php';
} );

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
