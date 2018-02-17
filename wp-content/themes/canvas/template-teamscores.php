<?php
/**
 * Template Name: Team Scores
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

							<h2>Team Scores:</h2>
							
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
							
							
							function dolls_compare($a, $b) {
								if ($a['points'] == $b['points']) {
									return 0;
								}
								return ($a['points'] > $b['points']) ? -1 : 1;    
							}

							foreach($sections as $section) {
								$sectionTeams = array(); ?>
								<table class="highestDollsSection">
									<tr><th class="sectionName"><?=$section->name?> Section</th><th>Games</th><th>Won</th><th>Draw</th><th>Lost</th><th>Dolls</th><th>Points</th></tr>
									<? $teams = $wpdb->get_results("SELECT * FROM ss_teams WHERE section_id = '{$section->id}'");
									foreach($teams as $team) {
										$teamStats = array(
											'name' => $team->name,
											'played' => 0,
											'points' => 0,
											'dolls' => 0,
											'won' => 0,
											'lost' => 0,
											'drawn' => 0
										);
										
										$teamPlayers = $wpdb->get_results("SELECT * FROM ss_players WHERE team_id = '{$team->id}'");
										
										for($i=1; $i<=18; $i++) {
											$thisTeamPlayedThisWeek = false;
											$matchQuery = "SELECT * FROM ss_matches WHERE week_number = '$i' AND home_team_id = '{$team->id}' AND `archived`='0'"; //Changed by JASHMALL 18Jun2014
											// Should really check here (and below) that the query only returns 1 row (this was the error we had before!). JAshmall 18Jun2014
											$match = $wpdb->get_row($matchQuery);
											if($match===null) {
												$matchQuery = "SELECT * FROM ss_matches WHERE week_number = '$i' AND away_team_id = '{$team->id}' AND `archived`='0'"; //Changed by JASHMALL 18Jun2014
												$match = $wpdb->get_row($matchQuery);
											}
											if($match!==null) {
												// First check if we are the home team
												if($match->home_team_id==$team->id) {
													// We are the home team, so add the home points to the total
													$teamStats['points']+= $match->home_team_points;
													// Check if this match has been linked up - if the away team is ID 0 then we can only guess the win/loss based on the home team's points and the midpoint 3
													if($match->away_team_id == 0) {
														if($match->home_team_points > 3) $teamStats['won']++;
														if($match->home_team_points == 3) $teamStats['drawn']++;
														if($match->home_team_points < 3) $teamStats['lost']++;
													} else {
														if($match->home_team_points > $match->away_team_points) $teamStats['won']++;
														if($match->home_team_points == $match->away_team_points) $teamStats['drawn']++;
														if($match->home_team_points < $match->away_team_points) $teamStats['lost']++;
													}
												} else {
													// We're the away team - add out match points to total
													$teamStats['points']+= $match->away_team_points;
													// We're the away team, so we must have a valid linked up match. Compare with home points
													if($match->home_team_points < $match->away_team_points) $teamStats['won']++;
													if($match->home_team_points == $match->away_team_points) $teamStats['drawn']++;
													if($match->home_team_points > $match->away_team_points) $teamStats['lost']++;
												}

  												
												foreach($teamPlayers as $player) {														
													$playerMatchScoreQuery = "SELECT * FROM ss_scores WHERE player_id = '{$player->id}' AND match_id = '{$match->id}'";
													$matchPlayerScore = $wpdb->get_row($playerMatchScoreQuery);
													$matchDolls = 0;													
													if(isset($matchPlayerScore->leg1_score) && isset($matchPlayerScore->leg2_score) && isset($matchPlayerScore->leg3_score)) {	
														// We've got a score line for this match which has all three legs defined; add to total team dolls
														$teamStats['dolls'] += $matchPlayerScore->leg1_score + $matchPlayerScore->leg2_score + $matchPlayerScore->leg3_score;
														$thisTeamPlayedThisWeek = true;
														
													}
												}
											}
											if($thisTeamPlayedThisWeek) {
												// We have a match result so this team must have played this week
												$teamStats['played']++;
											}
										}
										
										$sectionTeams[] = $teamStats;
									}
								
									
									usort($sectionTeams, "dolls_compare");
									
									foreach ($sectionTeams as $teamStats) { ?>
										<tr>
											<td><a href="http://abingdonauntsally.com/scoring-system/scores-and-results/all-scores/?sectionyear=<?=$sectionyear?>&season=<?=$season?>#<?=$teamStats['name']?>"><?=$teamStats['name']?></a></td>
											<td><?=$teamStats['played']?></td>
											<td><?=$teamStats['won']?></td>
											<td><?=$teamStats['drawn']?></td>
											<td><?=$teamStats['lost']?></td>
											<td><?=$teamStats['dolls']?></td>
											<td><?=$teamStats['points']?></td>
										</tr>
										<?
									}
								?>
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
