<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Related Products
 * Replace the default related products function with our own which displays the correct number of product columns
 * @since 5.7.0
 */
if (!function_exists('woo_wc_related_products')) {
	function woo_wc_related_products() {
		$products_max 	= 4;
		$products_cols 	= 4;
		$args = apply_filters( 'canvas_related_products_args', array(
			'posts_per_page' => $products_max,
			'columns'        => $products_cols,
		) );
		return $args;
	}
}

/**
 * Upsells
 * Replace the default upsell function with our own which displays the correct number product columns
 * @since   5.7.0
 * @return  void
 * @uses    woocommerce_upsell_display()
 */
if (!function_exists('woo_wc_upsell_display')) {
	function woo_wc_upsell_display() {
	    woocommerce_upsell_display( -1, 3 );
	}
}

if ( ! function_exists( 'loop_columns' ) ) {
	// Change columns in product loop to 4
	function loop_columns() {
		return 4;
	}
}

/**
 * Before Content
 * Wraps all WooCommerce content in wrappers which match the theme markup
 * @since   5.7.0
 * @return  void
 * @uses  	woo_content_before(), woo_main_before()
 */
if ( ! function_exists( 'woocommerce_canvas_before_content' ) ) {
	function woocommerce_canvas_before_content() {
	?>
		<!-- #content Starts -->
		<?php woo_content_before(); ?>
	    <div id="content" class="col-full">

	    	<div id="main-sidebar-container">

	            <!-- #main Starts -->
	            <?php woo_main_before(); ?>
	            <section id="main" class="col-left">
	    <?php
	}
}

/**
 * After Content
 * Closes the wrapping divs
 * @since   5.7.0
 * @return  void
 * @uses    woo_main_after(), woo_content_after()
 */
if ( ! function_exists( 'woocommerce_canvas_after_content' ) ) {
	function woocommerce_canvas_after_content() {
	?>
				</section><!-- /#main -->
	            <?php woo_main_after(); ?>

			</div><!-- /#main-sidebar-container -->

			<?php get_sidebar( 'alt' ); ?>

	    </div><!-- /#content -->
		<?php woo_content_after(); ?>
	    <?php
	}
}

/**
 * Breadcrumbs
 * Remove default breadcrumbs
 * @since   5.7.0
 * @return  void
 * @uses    woo_breadcrumbs()
 */
if ( ! function_exists( 'woocommerceframework_breadcrumb' ) ) {
	function woocommerceframework_breadcrumb() {
		global  $woo_options;
		if ( $woo_options['woo_breadcrumbs_show'] == 'true' ) {
			woo_breadcrumbs();
		}
	}
}

/**
 * WooCommerce Pagination
 * Replaces WooCommerce pagination with the function in the WooFramework
 * @uses  woocommerceframework_add_search_fragment()
 * @uses  woo_pagination()
 * @since  5.7.0
 * @return  void
 */
if ( ! function_exists( 'canvas_commerce_pagination' ) ) {
	function canvas_commerce_pagination() {
		if ( is_search() && is_post_type_archive() ) {
			add_filter( 'woo_pagination_args', 'woocommerceframework_add_search_fragment', 10 );
		}
		woo_pagenav();
	}
}

/**
 * Search Fragment
 * @param  array $settings Fragments
 * @return array           Fragments
 * @since  5.7.0
 */
if ( ! function_exists( 'woocommerceframework_add_search_fragment' ) ) {
	function woocommerceframework_add_search_fragment ( $settings ) {
		$settings['add_fragment'] = '&post_type=product';
		return $settings;
	} // End woocommerceframework_add_search_fragment()
}

/**
 * Search Widget
 * Customize output of search widget
 * @since   5.7.0
 * @return  string			Search Form
 */
if ( ! function_exists( 'woo_custom_wc_search' ) ) {
	function woo_custom_wc_search( $form ) {

		$form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
			<div>
				<label class="screen-reader-text" for="s">' . __( 'Search for:', 'woocommerce' ) . '</label>
				<input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __( 'Search', 'woothemes' ) . '" />
				<button type="submit" id="searchsubmit" class="fa fa-search submit" name="submit" value="' . __( 'Search', 'woothemes' ) . '"></button>
				<input type="hidden" name="post_type" value="product" />
			</div>
		</form>';

		return $form;

	} // End woo_custom_wc_search()
}

/**
 * Optionally display a header cart link next to the navigation menu.
 * @since  5.1.0
 * @return void
 */
if ( ! function_exists( 'woo_add_nav_cart_link' ) ) {
function woo_add_nav_cart_link () {
	global $woocommerce;
	$settings = array( 'header_cart_link' => 'false', 'nav_rss' => 'false', 'header_cart_total' => 'false' );
	$settings = woo_get_dynamic_values( $settings );

	$class = 'cart fr';
	if ( 'false' == $settings['nav_rss'] ) { $class .= ' no-rss-link'; }
	if ( is_woocommerce_activated() && 'true' == $settings['header_cart_link'] ) { ?>
    	<ul class="<?php echo esc_attr( $class ); ?>">
    		<li>
    			<a class="cart-contents" href="<?php echo esc_url( $woocommerce->cart->get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'woothemes' ); ?>">
					<?php if ( $settings['header_cart_total'] == 'true' ) { echo sprintf(_n('<span class="count">%d</span> item', '<span class="count">%d</span> items', $woocommerce->cart->get_cart_contents_count(), 'woothemes'), $woocommerce->cart->get_cart_contents_count() );?> - <?php echo $woocommerce->cart->get_cart_subtotal(); } ?>
    			</a>
    			<ul>
	    			<li>
		    			<?php
			       		if ( version_compare( WOOCOMMERCE_VERSION, "2.0.0" ) >= 0 ) {
							the_widget( 'WC_Widget_Cart', 'title=' );
						} else {
							the_widget( 'WooCommerce_Widget_Cart', 'title=' );
						} ?>
					</li>
				</ul>
    		</li>
   		</ul>
    <?php }
} // End woo_add_nav_cart_link()
}

/**
 * Inserts HTML5 shiv
 * @since  5.7.0
 * @return void
 */
if ( ! function_exists( 'woocommerce_html5' ) ) {
	function woocommerce_html5() {
		echo '<!--[if lt IE 9]><script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
	}
}

/**
 * If theme lightbox is enabled, disable the WooCommerce lightbox and make product images prettyPhoto galleries
 * @since  5.7.0
 * @return void
 */
function woocommerce_prettyphoto() {
	global $woo_options;
	if ( $woo_options[ 'woo_enable_lightbox' ] == "true" && is_product() ) {
		?>
			<script>
				jQuery(document).ready(function(){
					jQuery('.images a').attr('rel', 'prettyPhoto[product-gallery]');
				});
			</script>
		<?php
	}
}

/**
 * Star Rating (Sidebar)
 * @since  5.7.0
 * @return void
 */
if ( ! function_exists( 'woostore_star_sidebar' ) ) {
	function woostore_star_sidebar() {
		return 12;
	}
}

/**
 * Adjust the star rating in the recent reviews
 * @since  5.7.0
 * @return void
 */
if ( ! function_exists( 'woostore_star_reviews' ) ) {
	function woostore_star_reviews() {
		return 12;
	}
}

/**
 * Changes default image placeholder
 * @since  5.7.0
 * @return void
 */
if ( ! function_exists( 'wooframework_wc_placeholder_img_src' ) ) {
	function wooframework_wc_placeholder_img_src( $src ) {
		$settings = array( 'placeholder_url' => get_template_directory_uri() . '/images/wc-placeholder.gif' );
		$settings = woo_get_dynamic_values( $settings );

		return esc_url( $settings['placeholder_url'] );
	} // End wooframework_wc_placeholder_img_src()
}