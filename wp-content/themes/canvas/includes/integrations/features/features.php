<?php
/**
 * Integrates this theme with the Features by WooThemes plugin
 * http://wordpress.org/plugins/features-by-woothemes/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Support Declaration
 * @since   1.0.0
 * @return  void
 */
function woo_features_support() {
	add_theme_support( 'features-by-woothemes' );
}
add_action( 'after_setup_theme', 'woo_features_support' );

/**
 * Styles
 * @since   1.0.0
 * @return  void
 */
function woo_features_scripts() {
	wp_register_style( 'woo-features-css', get_template_directory_uri() . '/includes/integrations/features/css/features.css' );
	wp_enqueue_style( 'woo-features-css' );
}
add_action( 'wp_enqueue_scripts', 'woo_features_scripts', 10 );


/**
 * Customise Features
 * Change the default features columns to 3. Change the image size to 600.
 * @param  integer $args['per_row'] Number of columns to display
 * @param  integer $args['size'] image size
 * @return array Features args
 */
function woo_customise_features( $args ) {
	$args['per_row'] 	= 3;
	$args['size']		= 600;
	return $args;
}
add_filter( 'woothemes_features_default_args', 'woo_customise_features', 10 );