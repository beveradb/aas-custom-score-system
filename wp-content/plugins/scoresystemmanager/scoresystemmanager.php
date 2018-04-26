<?php
/*
Plugin Name: Score System Manager
Plugin URI: http://www.abingdonauntsally.com
Description: A plugin created to manage the Score System for Abingdon Aunt Sally Association.
Version: 1.0
Author: Andrew Beveridge
Author URI: http://www.andrewbeveridge.co.uk
License: GPLv3
*/

global $wpdb;

if (isset($_GET['debugtables'])) {
    $tablePrefix = 'ssd_';
} else {
    $tablePrefix = 'ss_';
}

if( isset( $_POST['weekNumber'] ) )
{
    echo $wpdb->replace(
        "{$tablePrefix}keyvalue",
        array(
            'key' => 'weekNumber',
            'value' => $_POST['weekNumber']
        ),
        array(
            '%s',
            '%s'
        )
    );
    
    die();
}


if(isset($_POST['forfeitMatchID'])) {
    $matchID = esc_sql($_POST['forfeitMatchID']);
    $match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE id = '$matchID'");

    $homeTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->home_team_id}'");
    $awayTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->away_team_id}'");

    if (isset($_POST['forfeitAction'])) {
        switch ($_POST['forfeitAction']) {
            case "setForfeitForHome":
                $wpdb->update(
                    "{$tablePrefix}matches",
                    array(
                        'home_forfeit' => 1
                    ),
                    array( 'ID' => $matchID )
                );

                break;
            case "setForfeitForAway":
                $wpdb->update(
                    "{$tablePrefix}matches",
                    array(
                        'away_forfeit' => 1
                    ),
                    array( 'ID' => $matchID )
                );
                break;
            case "clearForfeit":
                $wpdb->update(
                    "{$tablePrefix}matches",
                    array(
                        'home_forfeit' => 0,
                        'away_forfeit' => 0
                    ),
                    array( 'ID' => $matchID )
                );
                break;
        }

        $match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE id = '$matchID'");
    }
    ?>

    <strong>Home Team:</strong> <span class="homeTeamName"><?= $homeTeam->name ?></span> (Forfeit: <?=$match->home_forfeit?>)<br/>
    <strong>Away Team:</strong> <span class="awayTeamName"><?= $awayTeam->name ?></span> (Forfeit: <?=$match->away_forfeit?>)
    <br/>
    <br/>

    <a class="ssForfeitAction ssSetForfeitForHome thickbox button" href="" target="_blank"
       data-matchid="<?= $matchID ?>" data-action="setForfeitForHome">Set Forfeit for Home Team</a>
    <a class="ssForfeitAction ssSetForfeitForAway thickbox button" href="" target="_blank"
       data-matchid="<?= $matchID ?>" data-action="setForfeitForAway">Set Forfeit for Away Team</a>
    <a class="ssForfeitAction ssClearForfeit thickbox button" href="" target="_blank" data-matchid="<?= $matchID ?>"
       data-action="clearForfeit">Clear Forfeit</a>

    <script type="text/javascript">
        jQuery(function () {
            jQuery('.ssForfeitAction').on('click', function () {
                var forfeitMatchID = jQuery(this).data('matchid');
                var forfeitAction = jQuery(this).data('action');

                jQuery.post(document.href, {
                    "forfeitMatchID": forfeitMatchID,
                    "forfeitAction": forfeitAction
                }, function (data) {
                    jQuery("#forfeitDetailsSection").html(data);
                });

                return false;
            });
        });
    </script>

    <?php
    die();
}

function printMatchDetailsHTMLTables($matchID) {
    global $wpdb, $tablePrefix;
    $match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE id = '$matchID'");

    $homeTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->home_team_id}'");
    $awayTeam = $wpdb->get_row("SELECT * FROM {$tablePrefix}teams WHERE id = '{$match->away_team_id}'");

    $teamNames = array($homeTeam->id => $homeTeam->name, $awayTeam->id => $awayTeam->name);

    $matchScores = $wpdb->get_results(
        "SELECT * FROM {$tablePrefix}scores WHERE match_id = '$matchID' ORDER BY id ASC"
    );

    $venuesResults = $wpdb->get_results(
        "SELECT * FROM {$tablePrefix}venues ORDER BY id ASC",
        ARRAY_A
    );

    $playerNamesResults = $wpdb->get_results(
        "SELECT * FROM {$tablePrefix}players WHERE team_id IN ({$match->home_team_id}, {$match->away_team_id}) ORDER BY id ASC",
        ARRAY_A
    );

    $venueNames = array_column($venuesResults, "name", "id");
    $playerNames = array_column($playerNamesResults, "name", "id");
    $playerTeams = array_column($playerNamesResults, "team_id", "id");

    $userData = WP_User::get_data_by('id', $match->user_id);

    $homePointsAfterForfeits = $match->home_team_points;
    $awayPointsAfterForfeits = $match->away_team_points;

    $homePointsString = $match->home_team_points;
    $awayPointsString = $match->away_team_points;

    if($match->home_forfeit) {
        $homePointsAfterForfeits = 0;
        $awayPointsAfterForfeits = 6;
    }
    if($match->away_forfeit) {
        $homePointsAfterForfeits = 6;
        $awayPointsAfterForfeits = 0;
    }
    if($match->home_forfeit && $match->away_forfeit) {
        $homePointsAfterForfeits = 0;
        $awayPointsAfterForfeits = 0;
    }

    if($match->home_forfeit || $match->away_forfeit) {
        $homePointsString = "<del>{$match->home_team_points}</del> -> <strong>$homePointsAfterForfeits</strong>";
        $awayPointsString = "<del>{$match->away_team_points}</del> -> <strong>$awayPointsAfterForfeits</strong>";
    }

    ?>

    <table class='widefat'>
        <thead>
        <tr>
            <th>Submitted By User</th>
            <th>Home Team</th>
            <th>Away Team</th>
            <th>Venue</th>
            <th>Date</th>
            <th>Week Number</th>
            <th>Import Message</th>
            <th>Archived</th>
            <th>Home Points</th>
            <th>Away Points</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?=$userData->user_nicename?></td>
            <td><?=$teamNames[$match->home_team_id]?></td>
            <td><?=$teamNames[$match->away_team_id]?></td>
            <td><?=$venueNames[$match->venue_id]?></td>
            <td><?=DateTime::createFromFormat(DATE_ISO8601, $match->date)->format("d/m/Y")?></td>
            <td><?=$match->week_number?></td>
            <td><?=$match->import_message?></td>
            <td><?=$match->archived ? "<strong>Yes</strong>" : "No"?></td>
            <td><?=$homePointsString?></td>
            <td><?=$awayPointsString?></td>
        </tr>
        </tbody>
    </table>

    <br />

    <table class='widefat'>
        <thead>
        <tr><th>Team</th><th>Player Name</th><th>Leg 1</th><th>Leg 2</th><th>Leg 3</th></tr>
        </thead>
        <tbody>
        <?php
        foreach($matchScores as $matchScore) {
            echo "<tr><td>{$teamNames[$playerTeams[$matchScore->player_id]]}</td><td>{$playerNames[$matchScore->player_id]}</td><td>{$matchScore->leg1_score}</td><td>{$matchScore->leg2_score}</td><td>{$matchScore->leg3_score}</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <?php
}


if(isset($_POST['showScoreCardMatchID'])) {
    $matchID = esc_sql($_POST['showScoreCardMatchID']);
    printMatchDetailsHTMLTables($matchID);
    die();
}

if(isset($_POST['toggleArchiveMatchID'])) {
    $matchID = esc_sql($_POST['toggleArchiveMatchID']);
    $match = $wpdb->get_row("SELECT * FROM {$tablePrefix}matches WHERE id = '$matchID'");

    $wpdb->update(
        "{$tablePrefix}matches",
        array(
            'archived' => ((int) ! $match->archived)
        ),
        array( 'ID' => $matchID )
    );

    die();
}

if(isset($_POST['showAllScoreCardsTeamID']) && isset($_POST['showAllScoreCardsWeekNumber'])) {
    $teamID = esc_sql($_POST['showAllScoreCardsTeamID']);
    $weekNumber = esc_sql(trim($_POST['showAllScoreCardsWeekNumber']));

    $allMatches = $wpdb->get_results("SELECT * FROM {$tablePrefix}matches WHERE (home_team_id = '$teamID' OR away_team_id = '$teamID') AND week_number = '$weekNumber' ORDER BY id DESC");

    foreach($allMatches as $match) {
        echo "<strong>Match ID: {$match->id}</strong><br /><br />";
        printMatchDetailsHTMLTables($match->id);
        ?>
        <br />
        <a class="ssToggleArchiveMatch thickbox button" href="" target="_blank" data-matchid="<?=$match->id?>">Toggle Archived for this Match</a>

        <?php
        echo "<br /><hr /><br />";
    }
    ?>

    <script type="text/javascript">
        jQuery(function () {
            jQuery('.ssToggleArchiveMatch').on('click', function () {
                var toggleArchiveMatchID = jQuery(this).data('matchid');

                jQuery.post(document.href, {
                    "toggleArchiveMatchID": toggleArchiveMatchID
                }, function (data) {
                    jQuery('#showAllScoreCardsLoad').click();
                });

                return false;
            });
        });
    </script>

    <?php
    die();
}

// Hook for adding admin menus
add_action('admin_menu', 'scoreSystemManager_add_pages');

// action function for above hook
function scoreSystemManager_add_pages() {
    // Add a new top-level menu (ill-advised):
    add_menu_page('Score System', 'Score System', 'edit_pages', 'scoreSystemManager-dashboard', 'scoreSystemManager_dashboard');
}

// mt_toplevel_page() displays the page content for the custom Test Toplevel menu
function scoreSystemManager_dashboard() {
    global $wpdb;
	?>
    <!-- #content Starts -->
    <div id="content" class="col-full widedb">

    	<div id="main-sidebar-container">

            <!-- #main Starts -->
            <div id="main">      
                                                                                
                <!-- Post Starts -->
                <div class="post" style="max-width: 1000px;">
                    <br />
                    <br />
                    <h1>Score System Manager</h1>
                    <br />
                    
                    <?
                        $mostRecentSection = $wpdb->get_row("SELECT * FROM ss_sections ORDER BY id DESC LIMIT 1");
                        
                        $weekNumberValueRow = $wpdb->get_row("SELECT * FROM ss_keyvalue WHERE `key` = 'weekNumber'");
                        $selected = 0;
                        if($weekNumberValueRow != null)
                        {
                            $selected = $weekNumberValueRow->value;
                        }
                    ?>
                    <h2>Most recent season: <em><?=$mostRecentSection->season?> <?=$mostRecentSection->year?></em></h2>
                    <strong>Team Registration: <em><?=($mostRecentSection->open ? 'Open' : 'Closed')?></em></strong>
                    <br />
                    <br />

                    <h2>Current week number</h2>
                    <strong>Set to 0 to disable match card submission.</strong><br />
                    <select id="weekNumber" name="weekNumber">
                        <? for($i=0; $i<=20; $i++) { ?>
                            <option value="<?=$i?>" <?=($i == $selected ? 'selected="selected"' : '')?>><?=$i?></option>
                        <? } ?>
                    </select>
                    <button id="updateWeekNumber">Update</button>
                    <br />
                    <br />
                    
                    <? if(isset($_GET['editteam'])) { ?>
					<h2>Editing Players in Team with ID: <?=$_GET['editteam']?></h2>
					<script type="text/javascript"> var teamID = <?=$_GET['editteam']?>; </script>
					
					Use this table to add / edit players in a team. Delete is not allowed as scores may already have been entered for a player.<br />
					<br />
                    <div class="entry">
						<table cellpadding="0" cellspacing="0" border="0" class="display" id="ss_players" width="100%">
							<thead>
								<tr>
									<th>Team</th>
									<th>Name</th>
								</tr>
							</thead>
						</table>
                    </div><!-- /.entry -->
					<br />
					<br />
					<a class="button green" href="?page=scoreSystemManager-dashboard">Back to Score System Manager</a>
					<? } else { ?>
                        <script type="text/javascript"> var teamID = 0; </script>
                    <h2>Teams Table</h2>
					Use this table to modify teams. Click the link on the right of a row to manage the players in a specific team.<br />
					<br />
                    <div class="entry">
						<table cellpadding="0" cellspacing="0" border="0" class="display" id="ss_teams" width="100%">
							<thead>
								<tr>
									<th>Section</th>
									<th>Name</th>
									<th>Notes</th>
									<th>Players</th>
								</tr>
							</thead>
						</table>
                    </div><!-- /.entry -->
					
					<br />
                    <br />
                    <h2>League Sections Table</h2>
					<strong>To create a new league</strong>: Add rows for each section and set the registration column to "Open" for all of them.<br />
					<strong>To close registration for a league</strong>: Search for the relevant rows (e.g. "Summer 2013") and set the registration field for each of them to "Closed".<br />
					This table should probably only be modified at the start of each new season, and at the end of the team registration period for each season, to stop anyone registering a team after the season has begun.<br /> 
					<br />
                    <div class="entry">
						<table cellpadding="0" cellspacing="0" border="0" class="display" id="ss_sections" width="100%">
							<thead>
								<tr>
									<th>Year</th>
									<th>League Name / Season</th>
									<th>Section Name</th>
									<th>Registration</th>
								</tr>
							</thead>
						</table>
                    </div><!-- /.entry -->
					<br />
                    <br />
                    <h2>Venues Table</h2>
					Use this table to edit contact details for venues, and add new locations.<br />
					<strong>Attempting to delete a venue which has been used for matches will fail</strong>. There is no way to delete these venues, as they are required for historical reports.<br />
					In general, this table should not need to change much at all, as new venues only need to be added when the AASL finds a new pub/venue for matches, and venue contact details (phone number for example) are unlikely to change very often.<br />
					<br />
                    <div class="entry">
						<table cellpadding="0" cellspacing="0" border="0" class="display" id="ss_venues" width="100%">
							<thead>
								<tr>
									<th>Name</th>
									<th>Address</th>
									<th>Phone</th>
									<th>Postcode</th>
								</tr>
							</thead>
						</table>
                    </div><!-- /.entry -->

                    <br />
                    <br />

                    <h2>Match Editor:</h2>

                    Enter Match ID (from All Scores page): <input type="text" id="adminerMatchID" />
                    <a class="ssAdminerLink thickbox button" href="https://abingdonauntsally.com/wp-content/plugins/adminer/inc/adminer/loader.php?username=abingdon_wp&db=abingdon_wp&select=ss_matches&columns[0][fun]=&columns[0][col]=&where[0][col]=id&where[0][op]==&where[0][val]=adminerMatchID&where[01][col]=&where[01][op]==&where[01][val]=&order[0]=&limit=50&?KeepThis=true" target="_blank">Edit Team Results</a>
                    <a class="ssAdminerLink thickbox button" href="https://abingdonauntsally.com/wp-content/plugins/adminer/inc/adminer/loader.php?username=abingdon_wp&db=abingdon_wp&select=ss_scores&columns[0][fun]=&columns[0][col]=&where[0][col]=match_id&where[0][op]==&where[0][val]=adminerMatchID&where[01][col]=&where[01][op]==&where[01][val]=&order[0]=&limit=50&?KeepThis=true" target="_blank">Edit Player Scores</a>
                
                    <script type="text/javascript">
                        jQuery(function(){
                            jQuery('.ssAdminerLink').on('click', function() {
                                var matchID = jQuery('#adminerMatchID').val();

                                if(!jQuery.trim(matchID))
                                {
                                    alert("Please enter a match ID");
                                    return false;
                                }

                                jQuery(this).attr('href', jQuery(this).attr('href').replace("adminerMatchID", matchID));
                            });
                        });
                    </script>

                    <br />
                    <br />

                    <h2>Edit Forfeits for Match:</h2>

                    Enter Match ID (from All Scores page): <input type="text" id="forfeitMatchID" />
                    <a class="ssForfeitLoadMatch thickbox button" href="" target="_blank">Load Match</a>

                    <div id="forfeitDetailsSection"></div>

                    <script type="text/javascript">
                        jQuery(function(){
                            jQuery('.ssForfeitLoadMatch').on('click', function() {
                                var forfeitMatchID = jQuery('#forfeitMatchID').val();

                                if(!jQuery.trim(forfeitMatchID))
                                {
                                    alert("Please enter a match ID");
                                    return false;
                                }

                                jQuery.post(document.href, {"forfeitMatchID": forfeitMatchID}, function (data) {
                                    jQuery("#forfeitDetailsSection").html(data);
                                    jQuery('.ssClearForfeit')[0].scrollIntoView();
                                });

                                return false;
                            });
                        });
                    </script>

                    <br />

                    <h2>Show Individual Match (Scorecard) Detail by ID:</h2>

                    Enter Match ID (from All Scores page): <input type="text" id="showScoreCardMatchID" />
                    <a class="ssShowScoreCardLoadMatch thickbox button" href="" target="_blank">Load Match</a><br />
                    <br />

                    <div id="showScoreCardDetailsSection"></div>

                    <script type="text/javascript">
                        jQuery(function(){
                            jQuery('.ssShowScoreCardLoadMatch').on('click', function() {
                                var matchID = jQuery('#showScoreCardMatchID').val();

                                if(!jQuery.trim(matchID))
                                {
                                    alert("Please enter a match ID");
                                    return false;
                                }

                                jQuery.post(document.href, {"showScoreCardMatchID": matchID}, function (data) {
                                    jQuery("#showScoreCardDetailsSection").html(data);
                                });

                                return false;
                            });
                        });
                    </script>

                    <br />

                    <h2>Show All Match (Scorecard) Details by Team and Week:</h2>

                    Team: <select id="showAllScoreCardsTeam" name="showAllScoreCardsTeam">
                        <?
                        $sections = $wpdb->get_results("SELECT * FROM ss_sections ORDER BY `year` DESC, `season` DESC");

                        foreach ($sections as $section)
                        { ?>
                            <optgroup label="<?= stripslashes($section->season) ?> <?= stripslashes($section->year) ?> - <?= stripslashes($section->name) ?> Section">
                                <? $teams = $wpdb->get_results("SELECT * FROM ss_teams WHERE `section_id` = {$section->id} ORDER BY `name` ASC");
                                foreach ($teams as $team)
                                { ?>
                                    <option value="<?= $team->id ?>"><?= stripslashes($team->name) ?></option>
                                <? } ?>
                            </optgroup>
                        <? } ?>
                    </select>
                    Week Number: <input type="text" id="showAllScoreCardsWeek" />

                    <a id="showAllScoreCardsLoad" class="thickbox button" href="" target="_blank">Load Matches</a><br />
                    <br />

                    <div id="showAllScoreCardsSection"></div>

                    <script type="text/javascript">
                        jQuery(function(){
                            jQuery('#showAllScoreCardsLoad').on('click', function() {
                                var teamID = jQuery('#showAllScoreCardsTeam').val();
                                var weekNumber = jQuery('#showAllScoreCardsWeek').val();

                                if(!jQuery.trim(teamID))
                                {
                                    alert("Please select a team");
                                    return false;
                                }

                                if(!jQuery.trim(weekNumber))
                                {
                                    alert("Please enter a week number");
                                    return false;
                                }

                                jQuery.post(document.href, {"showAllScoreCardsTeamID": teamID, "showAllScoreCardsWeekNumber": weekNumber}, function (data) {
                                    jQuery("#showAllScoreCardsSection").html(data);
                                });

                                return false;
                            });
                        });
                    </script>

                    <br />
                    <br />

                    <? } ?>
				
                </div><!-- /.post -->
                <div class="fix"></div>
                                                                
            </div><!-- /#main -->

    </div><!-- /#content -->
	
	<link rel='stylesheet' href='/wp-content/themes/canvas/includes/css/jquery.dataTables.css'>
	<link rel='stylesheet' href='/wp-content/themes/canvas/includes/css/dataTables.tabletools.css'>
	<link rel='stylesheet' href='/wp-content/themes/canvas/includes/css/dataTables.editor.css'>
	<link rel='stylesheet' href='/wp-content/themes/canvas/includes/css/ColVis.css'>
	<script type="text/javascript" charset="utf-8" src="/wp-content/themes/canvas/includes/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/wp-content/themes/canvas/includes/js/jquery.dataTables.js"></script>
	<script type="text/javascript" language="javascript" src="/wp-content/themes/canvas/includes/js/dataTables.tabletools.min.js"></script>
	<script type="text/javascript" language="javascript" src="/wp-content/themes/canvas/includes/js/dataTables.editor.min.js"></script>
	<script type="text/javascript" language="javascript" src="/wp-content/themes/canvas/includes/js/ColVis.js"></script>
	<script type="text/javascript" language="javascript" src="/wp-content/themes/canvas/includes/js/ColReorderWithResize.js"></script>
	<script type="text/javascript" language="javascript" src="/wp-content/themes/canvas/includes/js/scoreSystemManager-db.js"></script>
	<?php
}

?>
