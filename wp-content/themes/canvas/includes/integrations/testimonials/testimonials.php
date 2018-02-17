<?php
/**
 * Integrates this theme with the Testimonials by WooThemes plugin
 * http://wordpress.org/plugins/testimonials-by-woothemes/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Styles
 */
function woo_testimonials_scripts() {
	wp_register_style( 'woo-testimonials-css', get_template_directory_uri() . '/includes/integrations/testimonials/css/testimonials.css' );
	wp_enqueue_style( 'woo-testimonials-css' );
}
add_action( 'wp_enqueue_scripts', 'woo_testimonials_scripts' );


/**
 * Customise Testimonials
 * Change the default testimonials columns to 3. Change the Gravatar size to 100.
 * @param  integer $args['per_row'] Number of columns to display
 * @param  integer $args['size'] Gravatar size
 * @return array Testimonials args
 */
function woo_customise_testimonials( $args ) {
	$args['per_row'] 	= 3;
	$args['size']		= 100;
	return $args;
}
add_filter( 'woothemes_testimonials_default_args', 'woo_customise_testimonials', 10 );

/**
 * Customise Testimonials Item Template
 * Move avatar image inside the testimonial text.
 * @param  string $tpl Testimonials template
 * @return string Testimonials template
 */
function woo_customise_testimonials_template( $tpl ) {
	$tpl = '<div id="quote-%%ID%%" class="%%CLASS%%" itemprop="review" itemscope itemtype="http://schema.org/Review"><blockquote class="testimonials-text" itemprop="reviewBody">%%AVATAR%% %%TEXT%%</blockquote>%%AUTHOR%%</div>';
	return $tpl;
}
add_filter( 'woothemes_testimonials_item_template', 'woo_customise_testimonials_template', 10 );
