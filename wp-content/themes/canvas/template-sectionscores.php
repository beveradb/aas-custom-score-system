<?php
/**
 * Template Name: Section Scores
 *
 * This page template shows all score cards for all matches in an Aunt Sally League
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
						
						<? if(!isset($_GET['sectionyear']) OR !isset($_GET['season'])) { ?>

							<? $seasons = $wpdb->get_results("SELECT DISTINCT season FROM ss_sections");
							$sectionyears = $wpdb->get_results("SELECT DISTINCT year FROM ss_sections ORDER BY year DESC"); ?>

							<h2>Section Scores:</h2>
							<form method="get" action="?">
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
						<? } else {
							$sectionyear = intval($_GET['sectionyear']);
							$season = $_GET['season']; 
							
							$sections = $wpdb->get_results("SELECT * FROM ss_sections WHERE year = '$sectionyear' AND season = '$season'");
							if(empty($sections)) die("This sectionyear / season combination has no results in the score system. <a href='?'>Please try again.</a>");
							
							function cmp($a, $b) {
								if ($a['total'] == $b['total']) {
									return 0;
								}
								return ($a['total'] > $b['total']) ? -1 : 1;
							}

							foreach($sections as $section) {
								$sectionPlayers = array(); ?>
								<table class="highestDollsSection">
									<tr><th colspan="2" class="sectionName"><?=$section->name?> Section</th><th>TOT</th><th>PLD</th><th>AVG</th></tr>
								<? $teams = $wpdb->get_results("SELECT * FROM ss_teams WHERE section_id = '{$section->id}'");
								foreach($teams as $team) { ?>
									
									<? $players = $wpdb->get_results("SELECT * FROM ss_players WHERE team_id = '{$team->id}' AND name NOT LIKE 'Handicap'");
									foreach($players as $player) {
										$playerTotalDolls = 0;
										$playerPlayedCount = 0;
										for($i=1; $i<=18; $i++) {
											$matchQuery = "SELECT * FROM ss_matches WHERE week_number = '$i' AND `archived`='0' AND (home_team_id = '{$team->id}' OR away_team_id = '{$team->id}')"; //Changed by JAshmall 19Jun2014
											$match = $wpdb->get_row($matchQuery);
											$playerScoresQuery = "SELECT * FROM ss_scores WHERE player_id = '{$player->id}' AND match_id = '{$match->id}'";
											$playerScores = $wpdb->get_row($playerScoresQuery);	
											if(isset($playerScores->leg1_score) && isset($playerScores->leg2_score) && isset($playerScores->leg3_score)) {														
												$playerTotalDolls += $playerScores->leg1_score + $playerScores->leg2_score + $playerScores->leg3_score;
												$playerPlayedCount++;
											}
										}
										$playerAverage = $playerPlayedCount ? number_format((float)$playerTotalDolls/($playerPlayedCount*3), 2, '.', '') : 0;
										$sectionPlayers[] = array(
																				'name' => $player->name,
																				'team' => $team->name,
																				'total' => $playerTotalDolls,
																				'played' => $playerPlayedCount,
																				'average' => $playerAverage
																			);
									}
								}
								// now we've populated the array of players in this section, lets sort said array and output the rows
								usort($sectionPlayers, "cmp");
								
								$sectionPlayers = array_slice($sectionPlayers, 0, 10);
								foreach($sectionPlayers as $player) { ?>
									<tr>
										<td><?=$player['name']?></td>
										<td><?=$player['team']?></td>
										<td><?=$player['total']?></td>
										<td><?=$player['played']?></td>
										<td class="playerAverage" style="background-color: hsl(120, 100%, <?=100-($player['average']*8.3)?>%);"><?=$player['average']?></td>
									</tr>
								<? } ?>
								
								</table>
						<?  } // end of allscores sections foreach loop ?>
							
					<?  } // end of else block checking for get parameters ?>
						
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
