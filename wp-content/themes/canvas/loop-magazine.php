<?php
/**
 * Loop - Magazine
 *
 * This is the loop logic file for the "Magazine" page template.
 *
 * @package WooFramework
 * @subpackage Template
 */

global $wp_query, $woo_options, $paged, $page, $post;
global $more; $more = 0;

remove_action( 'woo_post_inside_before', 'woo_display_post_image', 10 );

// woo_loop_before() is loaded in the main template, to keep the magazine slider out of this file.
$args = woo_get_magazine_query_args();
$query = new WP_Query( $args );
// Backup original wp_query variable, for safe-keeping.
$old_query = $wp_query;
$wp_query = $query;

if ( $query->have_posts() ) { $count = 0; $column_count_1 = 0; $column_count_2 = 0;
?>

<div class="fix"></div>
<?php
	while ( $query->have_posts() ) { $query->the_post(); $count++;
		// Featured Starts
		if ( isset( $woo_options['woo_magazine_feat_posts'] ) && $count <= $woo_options['woo_magazine_feat_posts'] && ! is_paged() ) {
			woo_get_template_part( 'content', 'magazine-featured' );
			continue;
		}

		$column_count_1++; $column_count_2++;
?>
		<div class="block<?php if ( $column_count_1 > 1 ) { echo esc_attr( ' last' ); $column_count_1 = 0; } ?>">
		<?php
			woo_get_template_part( 'content', 'magazine-grid' );
		?>
		</div><!--/.block-->
<?php

		if ( $column_count_1 == 0 ) { ?><div class="fix"></div><?php } // End IF Statement
	} // End WHILE Loop
} else {
	get_template_part( 'content', 'noposts' );
}

woo_loop_after();
woo_pagenav();

$wp_query = $old_query; // Restore wp_query variable.

add_action( 'woo_post_inside_before', 'woo_display_post_image', 10 );
?>