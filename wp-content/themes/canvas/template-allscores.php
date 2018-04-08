<?php
/**
 * Template Name: All Scores
 *
 * This page template shows all score cards for all matches in an Aunt Sally League
 * Amended PMJS - added Leg scores from match table on game popup
 * @package WooFramework
 * @subpackage Template
 */

 
 if(isset($_GET['debugtables'])) $tablePrefix = 'ssd_';
 else $tablePrefix = 'ss_';

if (isset($_POST['matchid'])) {
    $matchID = esc_sql($_POST['matchid']);
    $match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE id = '$matchID'");
    $user = $wpdb->get_row("SELECT * FROM wp_users WHERE ID= '$match->user_id'");

    $homeTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->home_team_id}'");
    $awayTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->away_team_id}'");
    ?>
    This value is from match ID <?= $matchID ?>: <?= $match->import_message ?><br/><br/>
    <strong><?= $homeTeam->name ?></strong> scored
    <strong><?= $match->home_forfeit ? 0 : $match->home_team_points ?></strong>
    (Legs <?= $match->home_team_total_leg1 ?>-<?= $match->home_team_total_leg2 ?>-<?= $match->home_team_total_leg3 ?>)
    <br/>
    <? if (!empty($awayTeam->name)) { ?>
        <strong><?= $awayTeam->name ?></strong> scored
        <strong><?= $match->away_forfeit ? 0 : $match->away_team_points ?></strong>
        (Legs <?= $match->away_team_total_leg1 ?>-<?= $match->away_team_total_leg2 ?>-<?= $match->away_team_total_leg3 ?>)
        <br/><br/>
    <? } else { ?>
        <strong>Away Team Unknown!</strong><br/>
        <em>Please contact us if you know who played against <?= $homeTeam->name ?> in week <?= $match->week_number ?>
            .</em><br/><br/>
    <? } ?>

    <strong>Forfeit:</strong>
        <?php
        if ($match->home_forfeit) {
            echo $homeTeam->name;
        } elseif ($match->away_forfeit) {
            echo $awayTeam->name;
        } else {
            echo "None";
        }
        ?><br />
    <br />

    <? if ($match->import_message == "Success") { ?>
        <em>This match data was imported from the old scores table, and linked up with the match reports with no
            errors.</em><br/>
    <? } elseif ($match->import_message == "Direct") { ?>
        <em>This match data was directly entered using the match card page
            on </em><?= $match->date ?>  by <?= $user->user_login ?><br/>
    <? } elseif (stripos($match->import_message, "NOMATCH") !== false) { ?>
        <em>This match data came from the old scores table, but could not be linked up with the match reports so does
            not have an "away" team.</em><br/>
    <? } elseif (stripos($match->import_message, "HOMESCOREFROMREPORT") !== false) { ?>
        <em>This match data was imported from the old scores table, but as the table did not have any points listed
            for <?= $homeTeam->name ?>, the points from the report were used.</em><br/>
    <? } elseif (stripos($match->import_message, "AWAYSCOREFROMREPORT") !== false) { ?>
        <em>This match data was imported from the old scores table, but as the table did not have any points listed
            for <?= $awayTeam->name ?>, the points from the report were used.</em><br/>
    <? } elseif (stripos($match->import_message, "TABLESCORES") !== false) { ?>
        <em>This match data was imported from the old scores table, but as the points in the report did not add up to 6,
            the points in the table were used. Please check, and get in touch with corrections!</em><br/>
    <? } elseif (stripos($match->import_message, "REPORTSCORES") !== false) { ?>
        <em>This match data was imported from the old scores table, but as the points in the table did not add up to 6,
            the points in the report were used. Please check, and get in touch with corrections!</em><br/>
    <? } elseif (stripos($match->import_message, "TABLESCORESNONSENSE") !== false) { ?>
        <em>This match data was imported from the old scores table, but as neither the table or report points added up
            to 6, the points in the table were used. Please check, and get in touch with corrections!</em><br/>
    <? }
    die();
}
 
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
						
							<? $seasons = $wpdb->get_results("SELECT DISTINCT season FROM {$tablePrefix}sections");
							$sectionyears = $wpdb->get_results("SELECT DISTINCT year FROM {$tablePrefix}sections ORDER BY year DESC"); ?>

							<h2>All Scores:</h2>
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
							
							$sections = $wpdb->get_results("SELECT * FROM {$tablePrefix}sections WHERE year = '$sectionyear' AND season = '$season'");
							if(empty($sections)) die("This year / season combination has no results in the score system. <a href='?'>Please try again.</a>");
							
							foreach($sections as $section) { ?>
								<h2><?=$section->name?> Section</h2>
								<? $teams = $wpdb->get_results("SELECT * FROM {$tablePrefix}teams WHERE section_id = '{$section->id}'");
								foreach($teams as $team) { ?>
									<table class="allScores">
										<tr><a name="<?=stripslashes($team->name)?>"></a>
											<th colspan="21" class="teamName"><?=$section->name?>: <?=stripslashes($team->name)?> 
 </th>
											<th class="winHeading"></th>
											<th class="drawHeading"></th>
											<th class="loseHeading"></th>
										</tr>
										<tr>
											<th class="pointsHeading">>>>>>>>TeamPoints</th>
											<? 	for($i=1; $i<=18; $i++) { 
													$matchFound = false;
													$match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE week_number = '$i' AND home_team_id = '{$team->id}' AND `archived`='0'");
													if(!empty($match)) {
														$homeTeamID = $team->id;
														$points = $match->home_forfeit ? 0 : $match->home_team_points;
														$matchFound = true;
													} else {
														$match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE week_number = '$i' AND away_team_id = '{$team->id}' AND `archived`='0'");
														if(!empty($match)) {
															$awayTeamID = $team->id;
															$points = $match->away_forfeit ? 0 : $match->away_team_points;
															$matchFound = true;
														}
													}
													if($matchFound) { 														
														$homeTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->home_team_id}'");
														$awayTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->away_team_id}'");

														?><td class='pointsValue matchTeamPointsHeading' data-matchID='<?=$match->id?>' title="Loading..."><?=$points?></td>
													<? } else { ?>
														<td class="playerScore week-<?=$i?>"></td>
													<? }
												} ?>
										</tr>
										<tr>
											<th class="weekHeading">Week</th>
											<? for($i=1; $i<=18; $i++) { echo "<td class='weekNumberHeading'>$i</td>"; } ?>
											<td class="playedHeading">PLD</td>
											<td class="totalHeading">TOT</td>
											<td class="averageHeading">AVG</td>
										</tr>
										<? $players = $wpdb->get_results("SELECT * FROM {$tablePrefix}players WHERE team_id = '{$team->id}'");
										foreach($players as $player) { 
											$playerPlayedCount = 0;
											$playerTotalDolls = 0;
										?>	<tr>
												<th class="playerName" data-playerid='<?=$player->id?>' title="Loading..."><?=$player->name?></th>
												<? for($i=1; $i<=18; $i++) {
													$matchQuery = "SELECT * FROM {$tablePrefix}matches WHERE week_number = '$i' AND (home_team_id = '{$team->id}' OR away_team_id = '{$team->id}')  AND `archived`='0'";
													$match = $wpdb->get_row($matchQuery);
													
													if(empty($match)) { ?>
														<td class="playerScore week-<?=i?>"></td>
													<? } else {
														$playerScoresQuery = "SELECT * FROM {$tablePrefix}scores WHERE player_id = '{$player->id}' AND match_id = '{$match->id}'";
														$playerScores = $wpdb->get_row($playerScoresQuery);	
														if(isset($playerScores->leg1_score) && isset($playerScores->leg2_score) && isset($playerScores->leg3_score)) {														
															$playerPlayedCount++;
															$playerTotalDolls += $playerScores->leg1_score + $playerScores->leg2_score + $playerScores->leg3_score;
															$playerMaxScore = max($playerScores->leg1_score, $playerScores->leg2_score, $playerScores->leg3_score);
														} else $playerMaxScore = 0;
													?>	<td class="playerScore maxScore<?=$playerMaxScore?> week-<?=$i?>"><span class="score<?=$playerScores->leg1_score?>"><?=$playerScores->leg1_score?></span><span class="score<?=$playerScores->leg2_score?>"><?=$playerScores->leg2_score?></span><span class="score<?=$playerScores->leg3_score?>"><?=$playerScores->leg3_score?></span></td>
												<?	}
												}
												$playerAverage = $playerPlayedCount ? number_format((float)$playerTotalDolls/($playerPlayedCount*3), 2, '.', '') : 0; ?>
												<td class="playerPlayed"><?=$playerPlayedCount?></td>
												<td class="playerTotal"><?=$playerTotalDolls?></td>
												<td class="playerAverage" style="background-color: hsl(120, 100%, <?=100-($playerAverage*8.3)?>%);"><?=$playerAverage?></td>
											</tr>
										<? } ?>
									</table>
								<? } ?>
						
						<?  } // end of allscores sections foreach loop ?>


                            <script>
                                jQuery(document).ready(function ($) {

                                    jQuery.widget.bridge('uitooltip', jQuery.ui.tooltip);

                                    jQuery('.matchTeamPointsHeading').uitooltip({
                                        content: "Please wait...",
                                        open: function (event, ui) {
                                            var _elem = ui.tooltip;
                                            $.ajax({
                                                url: document.URL,
                                                method: 'post',
                                                data: {
                                                    matchid: $(this).data('matchid')
                                                },
                                                success: function (data) {
                                                    _elem.find(".ui-tooltip-content").html(data);
                                                }
                                            });
                                        }
                                    });


                                    jQuery('.playerName').uitooltip({
                                        content: "Please wait...",
                                        open: function (event, ui) {
                                            var _elem = ui.tooltip.find(".ui-tooltip-content").html("Player ID: " + $(this).data('playerid'));
                                        }
                                    });
                                });
                            </script>

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
