<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------*/
/* Declare support for WooCommerce
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woocommerce_support' ) ) {
	function woocommerce_support() {
		add_theme_support( 'woocommerce' );
	}
}

/*-----------------------------------------------------------------------------------*/
/* Styles
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woo_load_woocommerce_css' ) ) {
	/**
	 * WooCommerce Styles
	 * Enqueue WooCommerce styles
	 */
	function woo_load_woocommerce_css () {
		wp_register_style( 'woocommerce', get_template_directory_uri() . '/includes/integrations/woocommerce/css/woocommerce.css' );
		wp_enqueue_style( 'woocommerce' );
	} // End woo_load_woocommerce_css()
}

if ( ! function_exists( 'woo_wc_disable_css' ) ) {
	function woo_wc_disable_css() {
		/**
		 * Disable WooCommerce styles
		 */
		if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
			// WooCommerce 2.1 or above is active
			add_filter( 'woocommerce_enqueue_styles', '__return_false' );
		} else {
			// WooCommerce is less than 2.1
			define( 'WOOCOMMERCE_USE_CSS', false );
		}
	}
}

/*-----------------------------------------------------------------------------------*/
/* Cart Fragment
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'woocommerce_header_add_to_cart_fragment' ) ) {
	function woocommerce_header_add_to_cart_fragment( $fragments ) {
		global $woocommerce;
		$settings = array( 'header_cart_link' => 'false', 'nav_rss' => 'false', 'header_cart_total' => 'false' );
		$settings = woo_get_dynamic_values( $settings );

		ob_start();
	?>
		<a class="cart-contents" href="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>">
			<?php if ( $settings['header_cart_total'] == 'true' ) { echo sprintf(_n('<span class="count">%d</span> item', '<span class="count">%d</span> items', $woocommerce->cart->get_cart_contents_count(), 'woothemes'), $woocommerce->cart->get_cart_contents_count() );?> - <?php echo $woocommerce->cart->get_cart_subtotal(); } ?>
		</a>
	<?php

		$fragments['a.cart-contents'] = ob_get_clean();

		return $fragments;
	} // End woocommerce_header_add_to_cart_fragment()
}

/*-----------------------------------------------------------------------------------*/
/* Install
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'woo_install_theme' ) ) {
	function woo_install_theme() {

		update_option( 'woocommerce_thumbnail_image_width', '200' );
		update_option( 'woocommerce_thumbnail_image_height', '200' );
		update_option( 'woocommerce_single_image_width', '500' ); // Single
		update_option( 'woocommerce_single_image_height', '500' ); // Single
		update_option( 'woocommerce_catalog_image_width', '400' ); // Catalog
		update_option( 'woocommerce_catalog_image_height', '400' ); // Catlog

	}
}