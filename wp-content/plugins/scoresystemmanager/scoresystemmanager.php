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

if( isset( $_POST['weekNumber'] ) )
{
    echo $wpdb->replace(
        'ss_keyvalue',
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
