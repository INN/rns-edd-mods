<?php

/* Plugin Name: RNS EDD Customizations
* Author: Russell Fair
* Version: 0.2
* Description: Integrates Easy Digital Downloads with the RNS content stack
*/

function rns_add_subscriber_enhancements(){
    require_once('lib/subscriber-extras.php');
    new RNS_Subscriber_Extras;
}
add_action('plugins_loaded', 'rns_add_subscriber_enhancements');

/**
 * Required for paywall and paywall exemptions
 */
require_once( plugin_dir_path( __FILE__ ) . 'class-rns-global.php' );
add_action( 'plugins_loaded', array( 'RNS_Global', 'get_instance' ) );

require_once( plugin_dir_path( __FILE__ ) . 'class-rns-national.php' );
add_action( 'plugins_loaded', array( 'RNS_National', 'get_instance' ) );

require_once( plugin_dir_path( __FILE__ ) . 'includes/class-rns-paywall.php' );
add_action( 'plugins_loaded', array( 'RNS\Paywall', 'get_instance' ) );

/**
 * Load Dashboard and administrative functionality
 */
if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-menu.php' );
	add_action( 'plugins_loaded', array( 'RNS\Admin_Menu', 'get_instance' ) );
}
