<?php
/**
 * Contains checks to see if plugins are active and then loads logic accordingly
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks if plugins are activated and loads logic accordingly
 * @uses  class_exists() detect if a class exists
 * @uses  function_exists() detect if a function exists
 * @uses  defined() detect if a constant is defined
 */

/**
 * Testimonials by WooThemes
 * http://wordpress.org/plugins/testimonials-by-woothemes/
 */
if ( class_exists( 'Woothemes_Testimonials' ) ) {
	require_once( get_template_directory() . '/includes/integrations/testimonials/testimonials.php' );
}

/**
 * Our Team by WooThemes
 * http://wordpress.org/plugins/our-team-by-woothemes/
 */
if ( class_exists( 'Woothemes_Our_Team' ) ) {
	require_once( get_template_directory() . '/includes/integrations/our-team/our-team.php' );
}

/**
 * Projects
 * @link http://wordpress.org/plugins/projects-by-woothemes/
 */
if ( class_exists( 'Projects' ) ) {
	require_once( get_template_directory() . '/includes/integrations/projects/setup.php' );
	require_once( get_template_directory() . '/includes/integrations/projects/template.php' );
	require_once( get_template_directory() . '/includes/integrations/projects/functions.php' );
}

/**
 * WooSlider by WooThemes
 * http://www.woothemes.com/products/wooslider/
 */
if ( class_exists( 'WooSlider' ) ) {
	if ( version_compare( get_option( 'wooslider-version' ), '2.0.2' ) >= 0 ) {
		require_once( get_template_directory() . '/includes/integrations/wooslider/wooslider.php' );
	}
}

/**
 * WooCommerce
 * @link http://wordpress.org/plugins/woocommerce/
 */
if ( is_woocommerce_activated() ) {
	require_once( get_template_directory() . '/includes/integrations/woocommerce/setup.php' );
	require_once( get_template_directory() . '/includes/integrations/woocommerce/template.php' );
	require_once( get_template_directory() . '/includes/integrations/woocommerce/functions.php' );
}

/**
 * Features by WooThemes
 * @link http://wordpress.org/plugins/features-by-woothemes/
 */
if ( class_exists( 'Woothemes_Features' ) ) {
	require_once( get_template_directory() . '/includes/integrations/features/features.php' );
}

/**
 * Archives by WooThemes
 * @link http://wordpress.org/plugins/archives-by-woothemes/
 */
if ( class_exists( 'Woothemes_Archives' ) ) {
	require_once( get_template_directory() . '/includes/integrations/archives/archives.php' );
}

/**
 * Subscribe and Connect by WooThemes
 * @link http://wordpress.org/plugins/subscribe-and-connect/
 */
if ( class_exists( 'Subscribe_And_Connect' ) ) {
	require_once( get_template_directory() . '/includes/integrations/subscribe-and-connect/subscribe-and-connect.php' );
}

/**
 * Sensei by WooThemes
 * @link http://www.woothemes.com/products/sensei/
 */
if ( class_exists( 'Woothemes_Sensei' ) ) {
	require_once( get_template_directory() . '/includes/integrations/sensei/setup.php' );
	require_once( get_template_directory() . '/includes/integrations/sensei/template.php' );
	require_once( get_template_directory() . '/includes/integrations/sensei/functions.php' );
}

/**
 * Gravity Forms
 * @link http://www.gravityforms.com
 */
if ( class_exists( 'GFForms' ) ) {
	require_once( get_template_directory() . '/includes/integrations/gravity-forms/gravity-forms.php' );
}

/**
 * Jetpack
 * @link http://wordpress.org/plugins/jetpack/
 */
if ( class_exists( 'Jetpack' ) ) {
	require_once( get_template_directory() . '/includes/integrations/jetpack/jetpack.php' );
}