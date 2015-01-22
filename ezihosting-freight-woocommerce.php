<?php
/*
Plugin Name: Australian EZi Freight
Plugin URI: http://www.ezihosting.com/woocommerce-australian-freight-extension/
Version: 0.0.1
Author: EZIHOSTING
Author URI: http://www.ezihosting.com.au/
Description: AusFreight, a great extension for WooCommerce that takes advantage of the lightweight Australian Post “Click & Send” flat rate sachets and combines this with the power of InterParcel’s brokerage strength for larger parcels. In the end, your customers will safe big bucks on their freight costs!
*/


register_activation_hook( __FILE__, 'efw_freight_activation' );
/**
 * Check the environment when plugin is activated
 *
 * Requirements:
 * - WooCommerce needs to be installed and activated
 * Note: this version of GCW is based on WooCommerce 1.4.4
 *
 * @since 0.9.0
 */
function efw_freight_activation() {

	$message = '';

	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$message .= sprintf( '<br /><br />%s', __( 'Install and activate the WooCommerce plugin first.', 'ezihosting_wc') );
	}

	if ( ! empty( $message ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		$message = __( 'Sorry! In order to use the freight for WooCommerce plugin you need to do the following:', 'ezihosting_wc' ) . $message;

		wp_die( $message, 'Freight For Woocommerce', array( 'back_link' => true ) );

	}

	efw_freight_create_table();
}

function efw_freight_create_table() {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix.'ezihosting_freight_tick_boxes';
	if($wpdb->get_var("show tables like '$table_name'") != $table_name){
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$table_name.'` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) DEFAULT NULL,
			`weight` decimal(10,2) NOT NULL,
			`length` decimal(10,2) NOT NULL,
			`width` decimal(10,2) NOT NULL,
			`height` decimal(10,2) NOT NULL,
			`max_weight` decimal(10,2) NOT NULL,
			`flat_rate` TINYINT(1) NOT NULL DEFAULT \'0\',
			`rate` decimal(10,2) NOT NULL DEFAULT \'0.00\',
			`is_deleted` tinyint(1) NOT NULL DEFAULT \'0\',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}
}

if ( ! defined( 'EFW_PLUGIN_DIR' ) )
	define( 'EFW_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

require_once EFW_PLUGIN_DIR.'/includes/class-efw-helper.php';
require_once EFW_PLUGIN_DIR.'/includes/class-efw-boxes.php';
require_once EFW_PLUGIN_DIR.'/includes/class-efw-box.php';
require_once EFW_PLUGIN_DIR.'/includes/class-efw-box-packer.php';

if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) )
	add_filter( 'woocommerce_shipping_methods', 'efw_shipping_add_method' );


function efw_shipping_add_method( $methods ) {
	require_once EFW_PLUGIN_DIR.'/includes/class-efw-shipping.php';

	$methods[] = 'EFWShipping';
	return $methods;
}

if ( is_admin() ) {
	require_once EFW_PLUGIN_DIR.'/includes/class-efw-boxes-list-table.php';
	require_once EFW_PLUGIN_DIR . '/admin/admin.php';
}