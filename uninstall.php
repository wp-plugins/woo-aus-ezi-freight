<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function efw_delete_plugin() {
	global $wpdb;

	delete_option( 'ezihosting_freight_config' );


	$table_name = $wpdb->prefix . "ezihosting_freight_tick_boxes1";

	$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}

efw_delete_plugin();