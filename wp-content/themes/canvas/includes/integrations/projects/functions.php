<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Styles
 * @since   1.0.0
 * @return  void
 */
function woo_projects_scripts() {
	wp_register_style( 'woo-projects-css', get_template_directory_uri() . '/includes/integrations/projects/css/projects.css' );
	wp_enqueue_style( 'woo-projects-css' );
}

/**
 * Support Declaration
 * @since   1.0.0
 * @return  void
 */
function woo_projects_support() {
	add_theme_support( 'projects-by-woothemes' );
}