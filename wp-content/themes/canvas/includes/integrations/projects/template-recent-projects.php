<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Recent Projects Template
 * Displays recent projects using the WooCommerce recent_products shortcode.
 *
 * @see woo_display_recent_projects()
 */
?>

<?php if ( class_exists( 'Projects' ) ) { ?>
	<section class="recent-projects">
		<h1 class="section-title"><?php _e( 'Recent Projects', 'woothemes' ); ?></h1>
		<?php
			$recent_projects_limit 				= apply_filters( 'woo_template_recent_projects_limit', $limit = 6 );
			$recent_projects_columns 			= apply_filters( 'woo_template_recent_projects_columns', $columns = 2 );
			$recent_projects_exclude_categories = apply_filters( 'woo_template_recent_projects_exclude_categories', $categories = null );
			echo do_shortcode( '[projects limit="' . $recent_projects_limit . '" columns="' . $recent_projects_columns . '" exclude_categories="' . $recent_projects_exclude_categories . '"]' );
		?>
	</section>
<?php }