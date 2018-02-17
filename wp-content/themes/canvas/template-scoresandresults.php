<?php
/**
 * Template Name: Scores and Results
 *
 * This page template shows all simple dropdown boxes to select parameters for displaying scores
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
						
						<? $seasons = $wpdb->get_results("SELECT DISTINCT season FROM ss_sections");
						$sectionyears = $wpdb->get_results("SELECT DISTINCT year FROM ss_sections ORDER BY year DESC"); ?>

						<h2>All Scores: Complete Players by Team week by week</h2>
						<form method="get" action="/scoring-system/scores-and-results/all-scores/">
							<label>Select Season: </label>
							<select id="sectionyearSelector" name="sectionyear">
								<? foreach($sectionyears as $sectionyear) { ?>
									<option value="<?=$sectionyear->year?>"><?=$sectionyear->year?></option>
								<? } ?>
							</select>
							<select id="seasonSelector" name="season">
								<? foreach($seasons as $season) { ?>
									<option value="<?=$season->season?>"><?=$season->season?></option>
								<? } ?>
							</select>
							<input type="submit"></input>
						</form>					
						
						<h2>Section Scores: TOP DOLLS</h2>
						<form method="get" action="/scoring-system/scores-and-results/section-scores/">
							<label>Select Season: </label>
							<select id="sectionyearSelector" name="sectionyear">
								<? foreach($sectionyears as $sectionyear) { ?>
									<option value="<?=$sectionyear->year?>"><?=$sectionyear->year?></option>
								<? } ?>
							</select>
							<select id="seasonSelector" name="season">
								<? foreach($seasons as $season) { ?>
									<option value="<?=$season->season?>"><?=$season->season?></option>
								<? } ?>
							</select>
							<input type="submit"></input>
						</form>			
						
						<h2>Team Scores: LEAGUE TABLES</h2>
						<form method="get" action="/scoring-system/scores-and-results/team-scores/">
							<label>Select Season: </label>
							<select id="sectionyearSelector" name="sectionyear">
								<? foreach($sectionyears as $sectionyear) { ?>
									<option value="<?=$sectionyear->year?>"><?=$sectionyear->year?></option>
								<? } ?>
							</select>
							<select id="seasonSelector" name="season">
								<? foreach($seasons as $season) { ?>
									<option value="<?=$season->season?>"><?=$season->season?></option>
								<? } ?>
							</select>
							<input type="submit"></input>
						</form>

                        <h2>Player Scores: Ranked By Doll Average</h2>
                        <form method="get" action="/scoring-system/scores-and-results/player-scores/">
                            <label>Select Season: </label>
                            <select id="sectionyearSelector" name="sectionyear">
                                <? foreach($sectionyears as $sectionyear) { ?>
                                    <option value="<?=$sectionyear->year?>"><?=$sectionyear->year?></option>
                                <? } ?>
                            </select>
                            <select id="seasonSelector" name="season">
                                <? foreach($seasons as $season) { ?>
                                    <option value="<?=$season->season?>"><?=$season->season?></option>
                                <? } ?>
                            </select>
                            <input type="submit"></input>
                        </form>

                        <h2>Player Scores: Ranked By Total Dolls</h2>
                        <form method="get" action="/scoring-system/scores-and-results/player-rank-by-total-dolls/">
                            <label>Select Season: </label>
                            <select id="sectionyearSelector" name="sectionyear">
                                <? foreach($sectionyears as $sectionyear) { ?>
                                    <option value="<?=$sectionyear->year?>"><?=$sectionyear->year?></option>
                                <? } ?>
                            </select>
                            <select id="seasonSelector" name="season">
                                <? foreach($seasons as $season) { ?>
                                    <option value="<?=$season->season?>"><?=$season->season?></option>
                                <? } ?>
                            </select>
                            <input type="submit"></input>
                        </form>

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