<?php
/**
 * Integrates this theme with the Subscribe & Connect by WooThemes plugin
 * http://wordpress.org/plugins/subscribe-and-connect/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Styles
 */
function woo_subscribe_and_connect_scripts() {
	wp_register_style( 'woo-subscribe-and-connect-css', get_template_directory_uri() . '/includes/integrations/subscribe-and-connect/css/subscribe-and-connect.css' );
	wp_enqueue_style( 'woo-subscribe-and-connect-css' );
}
add_action( 'wp_enqueue_scripts', 'woo_subscribe_and_connect_scripts' );