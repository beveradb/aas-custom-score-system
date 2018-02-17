<?php

/*
 * Editor server script for DB table ss_teams
 * Automatically generated by http://editor.datatables.net/generator
 */

// DataTables PHP library
include( "lib/DataTables.php" );

// Alias Editor classes so they are easy to use
use
	DataTables\Editor,
	DataTables\Editor\Field,
	DataTables\Editor\Format,
	DataTables\Editor\Join,
	DataTables\Editor\Validate;


// Build our Editor instance and process the data coming from _POST
$editor = Editor::inst( $db, 'ss_players' )
	->fields(
		Field::inst( 'team_id' ),
		Field::inst( 'name' )
	);
// The "process" method will handle data get, create, edit and delete
// requests from the client
$out = $editor
    ->process($_POST)
    ->data();

	
//	ORDER BY year DESC, season DESC, id ASC
// When there is no 'action' parameter we are getting data, and in this
// case we want to send extra data back to the client, with the options
// for the 'Sea' select list
if ( !isset($_POST['action']) ) {
	if(isset($_GET['teamID'])) {
		foreach ( $out['aaData'] as $aaDataID => $player ) {
			if($player['team_id']!=$_GET['teamID']) unset($out['aaData'][$aaDataID]);
		}
	}
	$out['aaData']=array_values($out['aaData']);
	
	foreach ( $out['aaData'] as $aaDataID => $player ) {
		$teamNameQueryString = "SELECT * FROM `ss_teams` WHERE `id` = '{$player['team_id']}' ";
		$teamName = $db->sql($teamNameQueryString)->fetch();
		$out['aaData'][$aaDataID]['team_name'] = $teamName['name'];
	}
	
	$teamDataQueryString = "SELECT `ss_teams`.`id` AS value, CONCAT(`ss_teams`.`name`, ' (', `ss_sections`.`year`, ' ', `ss_sections`.`season`, ' ', `ss_sections`.`name`, ')' ) AS label FROM `ss_teams`,`ss_sections` WHERE `ss_teams`.`section_id`=`ss_sections`.`id` ORDER BY `ss_sections`.year DESC, `ss_sections`.season DESC, `ss_sections`.id ASC";
	$teamData = $db->sql($teamDataQueryString)->fetchAll();
	$out['teamData'] = $teamData;
} elseif($_POST['action']=='create') {
	$teamNameQueryString = "SELECT * FROM `ss_teams` WHERE `id` = '{$_POST['data']['team_id']}'";
	$teamName = $db->sql($teamNameQueryString)->fetch();
	$out['row']['team_name'] = $teamName['name'];
} elseif($_POST['action']=='edit') {
	$teamNameQueryString = "SELECT * FROM `ss_teams` WHERE `id` = '{$_POST['data']['team_id']}'";
	$teamName = $db->sql($teamNameQueryString)->fetch();
	$out['row']['team_name'] = $teamName['name'];
}
 
// Send it back to the client
echo safe_json_encode( $out );
?>