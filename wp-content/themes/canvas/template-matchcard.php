<?php
/**
 * Template Name: Match Card
 *
 * This page template shows the user the form to submit their Aunt Sally Match Card
 *
 * @package WooFramework
 * @subpackage Template
 */

if (isset($_POST['teamID']))
{ 
    $teamID = intval($_POST['teamID']);
    $teamInfo = $wpdb->get_results("SELECT * FROM ss_teams WHERE id = '$teamID'");
    $teamSection = $wpdb->get_row("SELECT section_id FROM ss_teams WHERE id = '$teamID'");
    $sectionInfo = $wpdb->get_results("SELECT * FROM ss_sections WHERE id = '{$teamSection->section_id}'");
    $players = $wpdb->get_results("SELECT id,name FROM ss_players WHERE team_id = '$teamID'");
    echo json_encode(array('team' => $teamInfo, 'section' => $sectionInfo, 'players' => $players));
    die();
}

$weekNumberValueRow = $wpdb->get_row("SELECT * FROM ss_keyvalue WHERE `key` = 'weekNumber'");
$weekNumber = 0;
if ($weekNumberValueRow != null)
{
    $weekNumber = $weekNumberValueRow->value;
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

                    <?php
                    $current_user = wp_get_current_user();
                    $match_card= get_the_author_meta( 'MatchCard', $current_user->ID );
                    if (!is_user_logged_in())
                    {
                        $errors = '';
                        if (isset($_GET['wp-error']))
                        {
                            $errors = strip_tags($_GET['wp-error']);
                            $errors = str_ireplace('Lost your password?', '<a href="' . site_url('/wp-login.php?action=lostpassword') . '">Lost your password?</a>', $errors);
                            $errors = '<div class="pr-message pr-error"><p>' . $errors . '</p></div>';
                        } ?>

                        <p>You are required to login to view this page.</p>
                        <form style="text-align: left;" action="<? echo get_bloginfo('wpurl') ?>/wp-login.php" method="post">
                            <p>
                                <label for="log"><input type="text" name="log" id="log" value="<?= wp_specialchars(stripslashes($user_login), 1) ?>" size="22"/> Username</label><br/>
                                <label for="pwd"><input type="password" name="pwd" id="pwd" size="22"/> Password</label><br/>
                                <input type="submit" name="submit" value="Log In" class="button"/>
                                <label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever"/> Remember me</label><br/>
                            </p>
                            <input type="hidden" name="redirect_to" value="<?= $_SERVER['REQUEST_URI'] ?>"/>
                        </form>
                        <p>
                            <a href="<? echo get_bloginfo('wpurl') ?>/wp-register.php">Register</a> |
                            <a href="<? echo get_bloginfo('wpurl') ?>/wp-login.php?action=lostpassword">Lost your password?</a>
                        </p>

                    <? }
                     elseif (empty($match_card)) {
                         echo "You do not have permission to submit match cards - please contact your team leader to submit scores.<br />If you have any problems, please email either chairman or webmaster at (@) abingdonauntsally.com";
                     }
                    else { ?>

                    <?php woo_post_inside_before(); ?>

                        <h1 class="title"><?php the_title(); ?></h1>

                        <section class="entry">

                            <? if (isset($_POST['matchVenue'])) {
                                $date = DateTime::createFromFormat("d/m/Y", preg_replace('|[^0-9/]|', '', $_POST['date']));
                                $dateISO = $date->format('c');
                                $matchVenue = intval($_POST['matchVenue']);
                                $homeTeam = intval($_POST['homeTeam']);
                                $awayTeam = intval($_POST['awayTeam']);
                                
                                $scores = array();

                                foreach ($_POST['leg1Scores'] as $key => $value)
                                {
                                    if ($value !== '' && $value !== null)
                                    {
                                        $scores[intval($key)]['leg1_score'] = intval($value);
                                    }

                                    if ($value == 'X' || $value == 'x')
                                    {
                                        $scores[intval($key)]['leg1_score'] = 0;
                                    }
                                }
                                foreach ($_POST['leg2Scores'] as $key => $value)
                                {
                                    if ($value !== '' && $value !== null)
                                    {
                                        $scores[intval($key)]['leg2_score'] = intval($value);
                                    }

                                    if ($value == 'X' || $value == 'x')
                                    {
                                        $scores[intval($key)]['leg2_score'] = 0;
                                    }
                                }
                                foreach ($_POST['leg3Scores'] as $key => $value)
                                {
                                    if ($value !== '' && $value !== null)
                                    {
                                        $scores[intval($key)]['leg3_score'] = intval($value);
                                    }
                                    
                                    if ($value == 'X' || $value == 'x')
                                    {
                                        $scores[intval($key)]['leg3_score'] = 0;
                                    }
                                }

                                //echo "<pre>".print_r($scores,1)."</pre>"; die();

                                // Sum scores to calculate team points
                                $homeTotalScores = array('leg1_score' => 0, 'leg2_score' => 0, 'leg3_score' => 0);
                                $awayTotalScores = array('leg1_score' => 0, 'leg2_score' => 0, 'leg3_score' => 0);
                                foreach ($scores as $playerID => $legScores)
                                {
                                    $playerTeamID = $wpdb->get_var("SELECT team_id FROM ss_players WHERE id = '$playerID'");
                                    foreach ($legScores as $leg => $score)
                                    {
                                        if ($playerTeamID == $homeTeam) $homeTotalScores[$leg] += $score;
                                        else $awayTotalScores[$leg] += $score;
                                    }
                                }
                                $homePoints = 0;
                                $awayPoints = 0;
                                if ($homeTotalScores['leg1_score'] > $awayTotalScores['leg1_score'])
                                {
                                    $homePoints += 2;
                                }
                                elseif ($homeTotalScores['leg1_score'] < $awayTotalScores['leg1_score'])
                                {
                                    $awayPoints += 2;
                                }
                                else
                                {
                                    $homePoints += 1;
                                    $awayPoints += 1;
                                }

                                if ($homeTotalScores['leg2_score'] > $awayTotalScores['leg2_score'])
                                {
                                    $homePoints += 2;
                                }
                                elseif ($homeTotalScores['leg2_score'] < $awayTotalScores['leg2_score'])
                                {
                                    $awayPoints += 2;
                                }
                                else
                                {
                                    $homePoints += 1;
                                    $awayPoints += 1;
                                }

                                if ($homeTotalScores['leg3_score'] > $awayTotalScores['leg3_score'])
                                {
                                    $homePoints += 2;
                                }
                                elseif ($homeTotalScores['leg3_score'] < $awayTotalScores['leg3_score'])
                                {
                                    $awayPoints += 2;
                                }
                                else
                                {
                                    $homePoints += 1;
                                    $awayPoints += 1;
                                }


                                $wpdb->query("UPDATE ss_matches SET `archived`='1' WHERE (`home_team_id` = '$homeTeam' OR `home_team_id` = '$awayTeam') AND `week_number` = '$weekNumber'");
                           
                                
                                
 
                                $wpdb->insert(
                                    'ss_matches',
                                    array(
                                        'home_team_id' => $homeTeam,
                                        'home_team_points' => $homePoints,
                                        'away_team_id' => $awayTeam,
                                        'away_team_points' => $awayPoints,
                                        'venue_id' => $matchVenue,
                                        'date' => $dateISO,
                                        'week_number' => $weekNumber,
                                        'import_message' => 'Direct',
                                        'archived' => 0,
                                        'user_id'=>$current_user->ID,
					'home_team_total_leg1' => $homeTotalScores['leg1_score'],
					'home_team_total_leg2' => $homeTotalScores['leg2_score'],
					'home_team_total_leg3' => $homeTotalScores['leg3_score'],
					'away_team_total_leg1' => $awayTotalScores['leg1_score'],
					'away_team_total_leg2' => $awayTotalScores['leg2_score'],
					'away_team_total_leg3' => $awayTotalScores['leg3_score']
                                   )
                                );
/**
 * home_team_total_legx & away_team_total_legx added by Paul Stone to allow total leg scores to be stored and displayed
 */
                                $matchID = $wpdb->insert_id;

                                foreach ($scores as $player_id => $scores)
                                {
                                    $wpdb->insert("ss_scores", array(
                                            'player_id' => $player_id,
                                            'match_id' => $matchID,
                                            'leg1_score' => $scores['leg1_score'],
                                            'leg2_score' => $scores['leg2_score'],
                                            'leg3_score' => $scores['leg3_score']
                                        )
                                    );
                                }


                                echo "Thanks for submitting your scorecard. Your Match ID is: $matchID - Write this on your scorecard NOW and put it in the post! <br>";
                                echo "ALL SCORECARDS MUST BE ENTERED ONLINE AND POSTED TO REACH THE SECRETARY BY SATURDAY FOLLOWING THE MATCH. <br>Thank you!<br /><br /><a href='/'>Home</a>";

                            } else { ?>

                            <?php
                          
                            if (have_posts()) : while (have_posts()) : the_post(); ?>
                                <?php the_content(); ?>
                            <?php endwhile; endif; ?>


                            <? if ($weekNumber > 0) { ?>
                            
                            <div id="matchCard" style="clear: both;">
                                <form id="matchCardForm" action="?" method="POST">
                                    
                                    <table class="matchDetailsTable">
                                        <tr>
                                            <th class="matchVenueHeading">VENUE</th>
                                            <td><select id="matchVenue" name="matchVenue">
                                                    <? $venues = $wpdb->get_results("SELECT * FROM ss_venues ORDER BY name ASC");
                                                    foreach ($venues as $venue)
                                                    { ?>
                                                        <option value="<?= $venue->id ?>"><?= stripslashes($venue->name) ?></option>
                                                    <? } ?>
                                                </select></td>
                                                 
                                        </tr>
                                        <tr>
                                            <th class="homeTeamHeading">HOME TEAM</th>
                                            <td>
                                                <select id="homeTeam" name="homeTeam">
                                                    <?
                                                    $mostRecentSection = $wpdb->get_row("SELECT * FROM ss_sections ORDER BY id DESC LIMIT 1");
                                                    $sections = $wpdb->get_results("SELECT * FROM ss_sections WHERE `year` = " . $mostRecentSection->year . " ORDER BY `season` DESC");

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
                                            </td>
                                        </tr>
                                    </table>
                                    
                                     <table class="matchDetailsTable">
                                        <tr>
                                            <th class="matchVenueHeading">DATE</th>
                                            <td><input type="text" name="date" id="date" ></input></td>
                                            <th class="weekHeading">WEEK</th>
                                            <td><?= $weekNumber ?></td>
                                        </tr>
                                        <tr>
                                            <th class="awayTeamHeading">AWAY TEAM</th>
                                            <td colspan="3">
                                                <select id="awayTeam" name="awayTeam">
                                                    <?
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
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    
                                     
                                    <table id="LeftContainerTable" >
                                        <tr>
                                            <td>   <table id="homePlayerScores" class="playerScores">
                                        <tr class="scoresHeader">
                                            <th class="playersHeading">NAME</th>
                                            <th class="leg1Heading legHeading">1</th>
                                            <th class="leg2Heading legHeading">2</th>
                                            <th class="leg3Heading legHeading">3</th>
                                        </tr>
                                        <!-- The player scores rows will be added here by ajax -->
                                    </table></td>
                                        </tr>
                                            <tr>
                                                <td>
                                                    <table id="homeTotalScores">
                                        <tr>
                                            <th class="totalsHeading">TOTALS</th>
                                            <th class="homeLeg1Total inputBox">0</th>
                                            <th class="homeLeg2Total inputBox">0</th>
                                            <th class="homeLeg3Total inputBox">0</th>
                                        </tr>
                                    </table>
                                                    
                                                </td>
                                        </tr>
                                    </table>
                                    
                                         <table id="RightContainerTable" >
                                        <tr>
                                            <td>
                                                               <table id="awayPlayerScores" class="playerScores">
                                        <tr class="scoresHeader">
                                            <th class="playersHeading">NAME</th>
                                            <th class="leg1Heading legHeading">1</th>
                                            <th class="leg2Heading legHeading">2</th>
                                            <th class="leg3Heading legHeading">3</th>
                                        </tr>
                                        <!-- The player scores rows will be added here by ajax -->
                                    </table>
                                            </td>
                                        </tr>
                                            <tr>
                                                <td>
                                                       <table id="awayTotalScores">
                                        <tr>
                                            <th class="totalsHeading">TOTALS</th>
                                            <th class="awayLeg1Total inputBox">0</th>
                                            <th class="awayLeg2Total inputBox">0</th>
                                            <th class="awayLeg3Total inputBox">0</th>
                                        </tr>
                                    </table>
                                                    
                                                </td>
                                        </tr>
                                    </table>
                                 
                     

                                    
                                 
                                    <input type="submit" class="submit button s"></input>
                                </form>
                            </div>
                            <? } else { ?>

                                <div id="matchCard">
                                    <br />
                                    <br />
                                    <h2>Match card submission is currently disabled</h2>
                                    <h3>Please <a href="/about/committee/">contact the committee</a> if you need to submit a match card</h3>
                                </div>
                                
                            <? } ?>

                        </section><!-- /.entry -->

                        <script type="text/javascript" src=' http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js '></script>
                        <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/themes/base/jquery-ui.css"></link>

                        <script type="text/javascript">
                            jQuery(function ()
                            {
                                var currentTeams = [null, null];

                                jQuery("#homeTeam").prop("selectedIndex", -1);
                                jQuery("#awayTeam").prop("selectedIndex", -1);
                                jQuery("#matchVenue").prop("selectedIndex", -1);
                              jQuery("#date").datepicker({dateFormat: 'dd/mm/yy'});

                                jQuery(document).ajaxError(function (event, request, settings)
                                {
                                    alery("An error occurred while trying to load team data from URL: " + settings.url);
                                });

                                function loadTeamData(teamID)
                                {
                                    var teamData;
                                    jQuery.ajaxSetup({async: false});  //execute synchronously
                                    jQuery.post("/scoring-system/match-card/", "teamID=" + teamID, function (ajaxData)
                                    {
                                        teamData = ajaxData;
                                    }, "json");
                                    return teamData;
                                }

                                function updateTeamPlayerRows(playersData, playerScoresTable)
                                {
                                    jQuery(playerScoresTable + " .playerScoresRow").remove();
                                    jQuery.each(playersData, function (i, item)
                                    {
                                        jQuery("<tr class='playerScoresRow'>" +
                                            "<td class='playerName'>" + playersData[i].name + "</td>" +
                                            "<td class='playerScore1 inputBox'><input type='text' name='leg1Scores[" + playersData[i].id + "]'></input></td>" +
                                            "<td class='playerScore2 inputBox'><input type='text' name='leg2Scores[" + playersData[i].id + "]'></input></td>" +
                                            "<td class='playerScore3 inputBox'><input type='text' name='leg3Scores[" + playersData[i].id + "]'></input></td>" +
                                            "</tr>"
                                        ).appendTo(playerScoresTable);
                                    });
                                }
                                
                                function updatePopupTeamPlayerRows(playersData, playerScoresTable)
                                {
                                    jQuery(playerScoresTable + " .playerScoresRow").remove();
                                    jQuery.each(playersData, function (i, item)
                                    {
                                        jQuery("<tr class='playerScoresRow'>" +
                                            "<td class='playerName'>" + playersData[i].name + "</td>" +
                                            "<td class='playerScore1 inputBox'><input type='text' readonly='readonly' name='leg1Scores[" + playersData[i].id + "]'></input></td>" +
                                            "<td class='playerScore2 inputBox'><input type='text' readonly='readonly' name='leg2Scores[" + playersData[i].id + "]'></input></td>" +
                                            "<td class='playerScore3 inputBox'><input type='text' readonly='readonly'  name='leg3Scores[" + playersData[i].id + "]'></input></td>" +
                                            "</tr>"
                                        ).appendTo(playerScoresTable);
                                    });
                                }

                                function checkSections()
                                {
                                    var homeTeamName = currentTeams[0]['team'][0]['name'];
                                    var awayTeamName = currentTeams[1]['team'][0]['name'];
                                    var homeTeamSection = currentTeams[0]['section'][0];
                                    var awayTeamSection = currentTeams[1]['section'][0];

                                    if (currentTeams[0]['team'][0]['id'] == currentTeams[1]['team'][0]['id'])
                                    {
                                        alert("Teams cannot be the same. Please select the correct home and away teams");
                                        return 2;
                                    }
                                    /*
                                     if(homeTeamSection['id']!=awayTeamSection['id']&&homeTeamSection['name']!='A'&&awayTeamSection['name']!='A') {
                                     // Teams not in same section, alert user and return false
                                     alert(homeTeamName.replace(/\\/g, '')+" are in the section:\n\n     "+
                                     homeTeamSection['year']+" "+homeTeamSection['season']+" - "+
                                     homeTeamSection['name']+".\n\n"+awayTeamName.replace(/\\/g, '')+" are in the section:\n\n     "+
                                     awayTeamSection['year']+" "+awayTeamSection['season']+" - "+
                                     awayTeamSection['name']+".\n\n "+
                                     "Teams in section: "+homeTeamSection['name']+" can't play against teams in section: "+awayTeamSection['name']+" in a league match.");
                                     return 2;
                                     }*/

                                    // Both teams in same section, so show the team players
                                    updateTeamPlayerRows(currentTeams[0]['players'], "#homePlayerScores");
                                    updateTeamPlayerRows(currentTeams[1]['players'], "#awayPlayerScores");
                                    
                                    updatePopupTeamPlayerRows(currentTeams[0]['players'], "#homePlayerScoresPopup");
                                    updatePopupTeamPlayerRows(currentTeams[1]['players'], "#awayPlayerScoresPopup");
                                    return 0;
                                }

                                function teamChanged(teamWhichChanged, teamData)
                                {
                                    if (currentTeams[0] == null && currentTeams[1] == null)
                                    {
                                        //console.log("No teams have been selected, so just add this team to it's correct variable");
                                        currentTeams[teamWhichChanged] = teamData;
                                    }
                                    if (currentTeams[0] != null && currentTeams[1] == null && teamWhichChanged == 0)
                                    {
                                        //console.log("Home team has been changed but no away has been selected, no checks needed");
                                        currentTeams[teamWhichChanged] = teamData;
                                    }
                                    if (currentTeams[0] == null && currentTeams[1] != null && teamWhichChanged == 1)
                                    {
                                        //console.log(" Away team has been changed but no away has been selected, no checks needed");
                                        currentTeams[teamWhichChanged] = teamData;
                                    }
                                    if (currentTeams[0] == null && currentTeams[1] != null && teamWhichChanged == 0)
                                    {
                                        //console.log(" Home team selected for first time and away team already set, update and check sections");
                                        currentTeams[teamWhichChanged] = teamData;
                                        return checkSections();
                                    }
                                    if (currentTeams[0] != null && currentTeams[1] == null && teamWhichChanged == 1)
                                    {
                                        //console.log("Away team selected for first time and home team already set, update and check sections");
                                        currentTeams[teamWhichChanged] = teamData;
                                        return checkSections();
                                    }
                                    if (currentTeams[0] != null && currentTeams[1] != null)
                                    {
                                        //console.log("Both teams already set, but change was made, update and check sections");
                                        currentTeams[teamWhichChanged] = teamData;
                                        return checkSections();
                                    }
                                    return 1;
                                }

                                function checkErrorLevel(errorLevel)
                                {
                                    if (errorLevel === 0)
                                    {
                                        // The user managed to pick two valid teams! Woohoo! Show them the score sheet and submit button
                                        jQuery("#homePlayerScores, #awayPlayerScores, #homeTotalScores, #awayTotalScores, input.submit").show();
                                        addInputEvents();
                                    }
                                    else if (errorLevel === 2)
                                    {
                                        // The user selected a bad combination of teams - reset the selector completely
                                        jQuery("#homeTeam, #awayTeam").val([]);
                                        currentTeams = [null, null];
                                        jQuery("#homePlayerScores, #awayPlayerScores, #homeTotalScores, #awayTotalScores, input.submit").hide();
                                    }
                                }

                                function addInputEvents()
                                {
                                    // Calculate leg totals on every keypress 
                                    jQuery('.playerScoresRow input').keyup(function ()
                                    {
                                        // Catch enter key and cancel submit event
                                        if (event.keyCode == 13)
                                        {
                                            event.preventDefault();
                                            return false;
                                        }
                                        // Focus the next input for easy entry of many numbers, only when exactly 1 character
                                        if (jQuery(this).val().length == 1)
                                        {
                                            var inputs = jQuery(this).closest('form').find(':input');
                                            var currnetVal=jQuery(this).val()
                                            var objName=jQuery(this).attr('name');
                                             
                                            jQuery('#popupData [name="'+objName+'"]').val(currnetVal);
                                            inputs.eq(inputs.index(this) + 1).focus();
                                        }

                                        // Reset totals before recalculation
                                        jQuery('.homeLeg1Total,.homeLeg2Total,.homeLeg3Total,.awayLeg1Total,.awayLeg2Total,.awayLeg3Total').html('0');
                                        jQuery('.homeLeg1TotalPopup,.homeLeg2TotalPopup,.homeLeg3TotalPopup,.awayLeg1TotalPopup,.awayLeg2TotalPopup,.awayLeg3TotalPopup').html('0');
                                        // Add all the columns and display in the the totals heading at the bottom
                                        jQuery('#homePlayerScores td.playerScore1 input').each(function ()
                                        {
                                            jQuery('.homeLeg1Total').html(Number(jQuery('.homeLeg1Total').html()) + Number(jQuery(this).val()));
                                            jQuery('.homeLeg1TotalPopup').html(Number(jQuery('.homeLeg1TotalPopup').html()) + Number(jQuery(this).val()));
                                        });
                                        jQuery('#homePlayerScores td.playerScore2 input').each(function ()
                                        {
                                            jQuery('.homeLeg2Total').html(Number(jQuery('.homeLeg2Total').html()) + Number(jQuery(this).val()));
                                            jQuery('.homeLeg2TotalPopup').html(Number(jQuery('.homeLeg2TotalPopup').html()) + Number(jQuery(this).val()));
                                        });
                                        jQuery('#homePlayerScores td.playerScore3 input').each(function ()
                                        {
                                            jQuery('.homeLeg3Total').html(Number(jQuery('.homeLeg3Total').html()) + Number(jQuery(this).val()));
                                            jQuery('.homeLeg3TotalPopup').html(Number(jQuery('.homeLeg3TotalPopup').html()) + Number(jQuery(this).val()));
                                        });
                                        jQuery('#awayPlayerScores td.playerScore1 input').each(function ()
                                        {
                                            jQuery('.awayLeg1Total').html(Number(jQuery('.awayLeg1Total').html()) + Number(jQuery(this).val()));
                                            jQuery('.awayLeg1TotalPopup').html(Number(jQuery('.awayLeg1TotalPopup').html()) + Number(jQuery(this).val()));
                                        });
                                        jQuery('#awayPlayerScores td.playerScore2 input').each(function ()
                                        {
                                            jQuery('.awayLeg2Total').html(Number(jQuery('.awayLeg2Total').html()) + Number(jQuery(this).val()));
                                            jQuery('.awayLeg2TotalPopup').html(Number(jQuery('.awayLeg2TotalPopup').html()) + Number(jQuery(this).val()));
                                        });
                                        jQuery('#awayPlayerScores td.playerScore3 input').each(function ()
                                        {
                                            jQuery('.awayLeg3Total').html(Number(jQuery('.awayLeg3Total').html()) + Number(jQuery(this).val()));
                                            jQuery('.awayLeg3TotalPopup').html(Number(jQuery('.awayLeg3TotalPopup').html()) + Number(jQuery(this).val()));
                                        });
                                    });
                                }

                                function checkInputs()
                                {
                                    jQuery('#homePlayerScores input, #awayPlayerScores input').each(function ()
                                    {
                                        if (Number(jQuery(this).val()) > 6)
                                        {
                                            alert("A turn score greater than 6 has been entered somewhere - please recheck the scores.");
                                            return false;
                                        }
                                    });
                                    return true;
                                }

                                function checkSubmit()
                                {
                                    if (jQuery("#matchVenue").val() == null || jQuery("#date").val() == "")
                                    {
                                        alert("Please select the venue and date of this match.");
                                        return false;
                                    }
                                    if (jQuery(".playerScores input").filter(function ()
                                        {
                                            return this.value.length !== 0;
                                        }).length < 1)
                                    {
                                        alert("Too few scores have been entered - please fill in the score sheet fully before submitting.");
                                        return false;
                                    }
                                    if (checkInputs() == true)
                                    {
                                        //jQuery("#matchCardForm").submit();
                                        return true;
                                    }else
                                    {
                                        return false;
                                    }
                                }
                                
                                function checkSubmit1()
                                {
                                    if (jQuery("#matchVenue").val() == null || jQuery("#date").val() == "")
                                    {
                                        alert("Please select the venue and date of this match.");
                                        return false;
                                    }
                                    if (jQuery(".playerScores input").filter(function ()
                                        {
                                            return this.value.length !== 0;
                                        }).length < 1)
                                    {
                                        alert("Too few scores have been entered - please fill in the score sheet fully before submitting.");
                                        return false;
                                    }
                                    if (checkInputs() == true)
                                    {
                                        jQuery("#matchCardForm").submit();
                                        
                                    } 
                                }
                                    
                                jQuery(".close").click(function (event)
                                { jQuery('#popupData,#fade').css('display','none');
                                   
                                });    
                                // Add event handlers to make the team selection work
                                jQuery("#homeTeam").change(function ()
                                {
                                    var teamData = loadTeamData(jQuery("#homeTeam").val());
                                    checkErrorLevel(teamChanged(0, teamData));
                                });
                                jQuery("#awayTeam").change(function ()
                                {
                                    var teamData = loadTeamData(jQuery("#awayTeam").val());
                                    checkErrorLevel(teamChanged(1, teamData));
                                     
                                });
                                // Catch submit button press and check inputs before submitting
                                jQuery("#matchCardForm").submit(function (event)
                                {
                                    console.log('matchCardForm');
                                   // checkSubmit();
                                    //event.preventDefault();
                                });
                                jQuery("#popupData input.submit").click(function (event)
                                {
                                     checkSubmit1()
                                    event.preventDefault();
                                });
                                
                                jQuery("#matchCard input.submit").click(function (event)
                                {
                                    var stauts=checkSubmit();
                                     
                                    if(stauts==true){
                                        console.log('true ');
                                        jQuery('#popupData,#fade').css('display','block');
                                        
                                    }
                                 
                                    event.preventDefault();
                                });
                            });
                        </script>

                    <? } ?>

                        <?php woo_post_inside_after(); ?>

                    <? } /* End user-logged-in restriction */ ?>

                </article>
                <!-- /.post -->
                <?php woo_post_after(); ?>
                <div class="fix"></div>

            </section>
            <!-- /#main -->
            <?php woo_main_after(); ?>

            <?php get_sidebar(); ?>

        </div>
        <!-- /#main-sidebar-container -->

        <?php get_sidebar('alt'); ?>

    </div><!-- /#content -->
<?php woo_content_after(); ?>

<?php get_footer(); ?>

   <style type="text/css">
        .close{
                float: right;
                clear: both;
                font-size: 18px;
                 margin-top: 20px;
                 border: none;
background: #12b31c;
        }
        

        
       .black_overlay{
        display: none;
        position: absolute;
        top: 0%;
        left: 0%;
        width: 100%;
        height: 100%;
        background-color: black;
        z-index:10001;
        -moz-opacity: 0.8;
        opacity:.80;
        filter: alpha(opacity=80);
    }
    .white_content {
        display: none;
        position: absolute;
        top: 10%;
        left: 10%;
        width: 80%;
        height: 80%; 
        background-color: white;
        z-index:10002;
        overflow: auto;
    }
    </style>