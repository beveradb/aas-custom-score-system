<?php
/**
 * Integrates this theme with the Gravity Forms plugin
 * http://www.gravityforms.com/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Styles
 */
function woo_gravity_forms_scripts() {
	wp_register_style( 'woo-gravity-forms', get_template_directory_uri() . '/includes/integrations/gravity-forms/css/gravity-forms.css' );
	wp_enqueue_style( 'woo-gravity-forms' );
}
add_action( 'wp_enqueue_scripts', 'woo_gravity_forms_scripts', 50 );