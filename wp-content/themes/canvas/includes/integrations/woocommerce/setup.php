<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Integrates this theme with the WooCommerce plugin
 * http://www.woothemes.com/woocommerce/
 */

/**
 * General Setup
 */

add_action( 'after_setup_theme', 'woocommerce_support' );

if ( ! is_admin() ) {
	add_action( 'wp_enqueue_scripts', 'woo_load_woocommerce_css', 20 );
}

add_action( 'wp', 'woo_wc_disable_css' );

/**
 * Hook in on activation
 */
global $pagenow;
if ( is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) add_action( 'init', 'woo_install_theme', 1 );

/**
 * Layout
 * Replace WooCommerce wrappers with our own and filter the body class
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'woocommerce_canvas_before_content', 10 );
add_action( 'woocommerce_after_main_content', 'woocommerce_canvas_after_content', 20 );

/**
 * Upsells
 * Replace WooCommerce upsells with our own function which adjusts display
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woo_wc_upsell_display', 15 );

/**
 * Related Products
 * Filters related products to adjust display
 */
add_filter( 'woocommerce_output_related_products_args', 'woo_wc_related_products' );

/**
 * Product Columns
 * Filters product columns to adjust display
 */
add_filter( 'loop_shop_columns', 'loop_columns' );

/**
 * Breadcrumbs
 * Remove the WooCommerce breadcrumb. The WooFramework breadcrumb is hooked in later.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
add_action( 'woocommerce_before_main_content', 'woocommerceframework_breadcrumb', 20, 0 );

/**
 * Sidebar
 * Replace the WooCommerce sidebar with our own.
 */
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_action( 'woo_main_after', 'woocommerce_get_sidebar', 10 );

/**
 * Pagination
 * Replace the WooCommerce pagination function with woo_pagination.
 */
remove_action( 'woocommerce_pagination', 'woocommerce_pagination', 10 ); // < 2.0
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 ); // 2.0 +
add_action( 'woocommerce_after_main_content', 'canvas_commerce_pagination', 01, 0 );

/**
 * Cart Fragments
 * Pieces of code to refresh via ajax when products are added to the cart
 */
add_filter( 'add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );

/**
 * Search Widget
 * Customize output of search form
 */
add_filter( 'get_product_search_form', 'woo_custom_wc_search' );

/**
 * Header Cart
 * Optionally display a header cart link next to the navigation menu.
 */
add_action( 'woo_nav_inside', 'woo_add_nav_cart_link', 20);

/**
 * HTML5
 * Adds HTML5 shiv
 */
add_action('wp_head', 'woocommerce_html5');

/**
 * PrettyPhoto
 * Disable the WooCommerce lightbox and make product images prettyPhoto galleries
 */
add_action( 'wp_footer', 'woocommerce_prettyphoto' );

/**
 * Products per page
 * Change default number of products per page
 */
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 12;' ) );

/**
 * Reviews
 * Remove default review stuff - the theme overrides it
 */
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

/**
 * Star Rating (Sidebar)
 * Adjust the star rating in the sidebar
 */
add_filter( 'woocommerce_star_rating_size_sidebar', 'woostore_star_sidebar' );

/**
 * Star Rating (Recent Reviews)
 * Adjust the star rating in the recent reviews
 */
add_filter( 'woocommerce_star_rating_size_recent_reviews', 'woostore_star_reviews' );

/**
 * Image Placeholder
 * Changes default image placeholder
 */
add_filter( 'woocommerce_placeholder_img_src', 'wooframework_wc_placeholder_img_src' );