<?php
/**
 * Integrates this theme with the Our Team plugin
 * http://wordpress.org/plugins/our-team-by-woothemes/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Styles
 */
function woo_our_team_scripts() {
	wp_register_style( 'woo-our-team-css', get_template_directory_uri() . '/includes/integrations/our-team/css/our-team.css' );
	wp_enqueue_style( 'woo-our-team-css' );
}
add_action( 'wp_enqueue_scripts', 'woo_our_team_scripts', 10 );