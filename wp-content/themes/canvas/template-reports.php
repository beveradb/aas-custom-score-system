<?php
/**
 * Template Name: Reports
 *
 * This page template shows the user statistics and scores in Aunt Sally Leagues
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options;
 get_header();
?>
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">

    	<div id="main-sidebar-container">

            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <section id="main">

				<?php woo_loop_before(); ?>
                <!-- Post Starts -->
                <?php woo_post_before(); ?>
                <article class="post">

                    <?php woo_post_inside_before(); ?>

                    <h1 class="title"><?php the_title(); ?></h1>
					
                    <section class="entry">					
							
						<?php if (have_posts()) : while (have_posts()) : the_post();?>
						<?php the_content(); ?>
						<?php endwhile; endif; ?>
						
						<h2>Player Stats</h2>
						
						<select id="teamSelector" name="teamID">
							<? $teams = $wpdb->get_results("SELECT * FROM ss_teams");
							foreach($teams as $team) { ?>
								<option value="<?=$team->id?>"><?=stripslashes($team->name)?></option>
							<? } ?>
						</select>
						
						
						<h2>Team Stats</h2>
						
						<select id="teamSelector" name="teamID">
							<? $teams = $wpdb->get_results("SELECT * FROM ss_teams");
							foreach($teams as $team) { ?>
								<option value="<?=$team->id?>"><?=stripslashes($team->name)?></option>
							<? } ?>
						</select>
						
												
                    </section><!-- /.entry -->

                    <?php woo_post_inside_after(); ?>

                </article><!-- /.post -->
                <?php woo_post_after(); ?>
                <div class="fix"></div>

            </section><!-- /#main -->
            <?php woo_main_after(); ?>

            <?php get_sidebar(); ?>

		</div><!-- /#main-sidebar-container -->

		<?php get_sidebar( 'alt' ); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>