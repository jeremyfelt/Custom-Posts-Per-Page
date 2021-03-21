<?php
/*  Uninstall file for the Custom Posts Per Page plugin. */
/*  The only settings added by Custom Posts Per Page are under the option names
 *  'cpppc_options' and 'cpppc_upgrade'. Not much to do for cleanup, but here it is. */

/*  Check to make sure this file has been called by WordPress and not through any
 *  kind of direct link. */
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/*  Delete the cpppc_options */
delete_option( 'cpppc_options' );
delete_option( 'cpppc_upgrade' );
