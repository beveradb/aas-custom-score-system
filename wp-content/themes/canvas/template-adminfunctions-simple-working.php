<?php
/**
 * Template Name: Admin Functions Old Simple
 *
 * This page template is only for developer usage (by Andrew!)
 *
 * @package WooFramework
 * @subpackage Template
 */
 
 if(isset($_GET['blogtransfer'])) {
	// THIS ADMIN FUNCTION IS USED TO MIGRATE POSTS FROM THE OLD SITE (SOHOADMIN CMS) TO WORDPRESS
 
	echo "<pre>";
	$categoryID = 6;
	$whereClause = "WHERE `BLOG_SUBJECT` = '3' OR `BLOG_SUBJECT` = '6' OR `BLOG_SUBJECT` = '8' OR `BLOG_SUBJECT` = '11' OR `BLOG_SUBJECT` = '13' OR `BLOG_SUBJECT` = '15'";
	//$whereClause = "WHERE `BLOG_SUBJECT` = '5' OR `BLOG_SUBJECT` = '7' OR `BLOG_SUBJECT` = '9' OR `BLOG_SUBJECT` = '12' OR `BLOG_SUBJECT` = '14'"; //summer
	$userID = 1;
 
	$results = $wpdb->get_results("SELECT `BLOG_TITLE`,`BLOG_DATA`,`timestamp` FROM `BLOG_CONTENT` $whereClause ORDER BY `BLOG_CONTENT`.`BLOG_DATE` DESC");
	
	foreach($results as $result) {
		$origcontent = $result->BLOG_DATA;
		$title = $result->BLOG_TITLE;
		$date = date("Y-m-d H:i:s", $result->timestamp);
		$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
		$slug = preg_replace('|-+|si', '-', $slug);
		$slug = preg_replace('|-$|si', '', $slug);
		
		$content = preg_replace('|/images/|si', '/wp-content/uploads/2013/09/', $origcontent);
		$content = preg_replace('|JPG|si', 'jpg', $content);
		$content = preg_replace('|<span.*?>|si', '', $content);
		$content = preg_replace('|<font.*?>|si', '', $content);
		$content = preg_replace('|<div.*?>|si', '', $content);
		$content = preg_replace('|<br.*?>|si', '', $content);
		$content = preg_replace('|<place.*?>|si', '', $content);
		$content = preg_replace('|<city.*?>|si', '', $content);
		$content = preg_replace('|<o:.*?>|si', '', $content);
		$content = preg_replace('|</span.*?>|si', '', $content);
		$content = preg_replace('|</font.*?>|si', '', $content);
		$content = preg_replace('|</div.*?>|si', '', $content);
		$content = preg_replace('|</place.*?>|si', '', $content);
		$content = preg_replace('|</city.*?>|si', '', $content);
		$content = preg_replace('|</o:.*?>|si', '', $content);
		$content = preg_replace('|class=".*?"|si', '', $content);
		$content = preg_replace('|style=".*?"|si', '', $content);
		$content = preg_replace('|nowrap=".*?"|si', '', $content);
		$content = preg_replace('|border=".*?"|si', '', $content);
		$content = preg_replace('|cellspacing=".*?"|si', '', $content);
		$content = preg_replace('|cellpadding=".*?"|si', '', $content);
		$content = preg_replace('|valign=".*?"|si', '', $content);
		$content = preg_replace('|align=".*?"|si', '', $content);
		$content = preg_replace('|width=".*?"|si', '', $content);
		$content = preg_replace('|height=".*?"|si', '', $content);
		$content = preg_replace('|&nbsp;|si', ' ', $content);
		$content = preg_replace('|[ ]+>|si', '>', $content);
		$content = preg_replace('|<[a-z]*?>\s*</[a-z]*?>|si', '', $content);
		
		$opts = array(	"show-body-only" => true, 
						"clean" => true, 
						"output-xhtml" => true,
						"indent" => true,
						"indent-spaces" => 4,
						"wrap" => 0
				);
		$content = tidy_parse_string($content, $opts);

		$content = esc_sql($content);
		
		echo "\n\n - - - - BEGINNING INSERTION OF NEW BLOG POST WITH TITLE: '$title' \n\n";
		
		$insertQuery = "INSERT INTO `wp_posts` (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES($userID, '$date', '$date', '$content', '$title', '', 'publish', 'open', 'closed', '', '$slug', '', '', '$date', '$date', '', 0, '', 0, 'post', '', 0)";
		//echo "\n".$insertQuery."\n";
		
		if($wpdb->query( $insertQuery ) === false) {
			echo "\n\n\nINSERT FAILED FOR QUERY: $insertQuery\n\n\n"; die;  
		}
		
		$id = $wpdb->insert_id;
		
		$updateQuery = "UPDATE `wp_posts` SET `guid` = 'https://abingdonauntsally.com/?p=$id' WHERE `ID` = '$id'";
		echo $updateQuery."\n";
		$wpdb->query( $updateQuery );
		
		$metaQuery = "INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES
						($id, '_edit_last', '$userID'),
						($id, '_edit_lock', '{$result->timestamp}:$userID')";
		$wpdb->query( $metaQuery );
		echo $metaQuery."\n";
		
		$taxonomyQuery = "INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ($id, $categoryID, 0)";
		$wpdb->query( $taxonomyQuery );
		echo $taxonomyQuery."\n";

		echo "\n\n - - - - SUCCESSFULLY MIGRATED blog post ID: $id \n";
	}
	 
	
	echo "</pre>";
	die();
 }
 
 
 if(isset($_GET['readscores'])) {
	// THIS IF BLOCK IS FOR PARSING THE SCORES FROM THE HTML TABLES IN THE OLD ALL SCORES PAGES INTO THE DATABASE 
	$sectionsArray = array();
	$year = '2013';
 
	$filepath = "/home/abingdon/public_html/oldresults/$year/all-scores.htm";
	$file = file_get_contents($filepath);
	
	//print_r($file); die();
	
	$file = preg_replace('|<html><body>(.+)</body></html>|si', '$1', $file);
	$file = preg_replace('|<br />|si', '', $file);
	$file = preg_replace('|<hr />|si', '', $file);
	$file = preg_replace('|<table.*?>|si', '~TEAMSTART~', $file);
	$file = preg_replace('|</table>|si', '~TEAMEND~', $file);
	$file = preg_replace('|<h2>(.+?) Section</h2>|si', "~SECTIONEND~\n~SECTIONNAME~$1~", $file);
	$file = preg_replace('| align=.*?>|si', '>', $file);
	$file = preg_replace('| colspan=.*?>|si', '>', $file);
	$file = preg_replace('| width=.*?>|si', '>', $file);
	$file = preg_replace('|&nbsp;|si', '', $file);
	$file = preg_replace('|>-<|si', '><', $file);
	$file = preg_replace('|<b>|si', '', $file);
	$file = preg_replace('|</b>|si', '', $file);
	$file = preg_replace('|<i>|si', '', $file);
	$file = preg_replace('|</i>|si', '', $file);
	$file = preg_replace('|Total Points: ([0-9]+)|si', '$1', $file);
	$file = preg_replace('|<tr>|si', '', $file);
	$file = preg_replace('|</tr>|si', '', $file);
	$file = preg_replace('|</td>|si', ',', $file);
	$file = preg_replace('|<td>|si', '', $file);
	$file = preg_replace('|<td>|si', '', $file);
	$file = preg_replace('|^~SECTIONEND~\n(.+)\n$|si', '$1~SECTIONEND~', $file);
	
	preg_match_all("|~SECTIONNAME~(.+?)~\n(.+?)\n~SECTIONEND~|si", $file, $sections);
	
	
	$sectionNames = $sections[1];
	$sectionStrings = $sections[2];
	
	foreach($sectionNames as $key => $sectionName) {
		preg_match_all("|~TEAMSTART~\n(.+?)\n~TEAMEND~|si", $sectionStrings[$key], $teams);
		
		$teamStringsArray = $teams[1];
		foreach($teamStringsArray as $teamString) {
			//$teamString = preg_replace('|,$|mi', "", $teamString);
			
			$teamLines = explode("\n",$teamString);
			$lineData = array();
			foreach($teamLines as $key => $teamLine) {
				$lineData[$key] = str_getcsv($teamLine);
			}
			
			$sectionsArray[$sectionName][$lineData[0][0]] = $lineData;
		}

	}
	
	//print_r($sectionsArray); die(); // BADASS MULTIDIMENSIONAL ARRAY OF ALL SECTIONS IN THIS LEAGUE, WITH TEAM NAMES MAPPED TO ROWS, THEN COLUMNS OF DATA
	// Now let's submit this shizzle to the database
	$teamMatchIDs = array();
	$output = "";
	$success = "";
	
	foreach($sectionsArray as $section => $teams) {
		
		$query = "INSERT INTO `ss_sections` (`name`, `year`, `season`, `open`) VALUES ('$section', '$year', 'Summer', '0')";
		$output .= $query."\n";
		if(!$wpdb->query($query)) {
			$output .= "FAILED QUERY: \n$query\n"; die($output);
		}
		$sectionID = $wpdb->insert_id;
	
		foreach($teams as $teamName => $teamRows) {
			$teamName = esc_sql($teamName);
			$query = "INSERT INTO `ss_teams` (`name`, `section_id`) VALUES ('$teamName', '$sectionID')";
			$output .= $query."\n";
			if(!$wpdb->query($query)) {
				$output .= "FAILED QUERY: \n$query\n"; die($output);
			}
			$teamID = $wpdb->insert_id;
		
			for($i=1; $i<=18; $i++) {
				$query = "INSERT INTO `ss_matches` (`home_team_id`, `home_team_points`, `week_number`) VALUES ('$teamID', '{$teamRows[1][$i]}', '$i')";
				$output .= $query."\n";
				if(!$wpdb->query($query)) {
					$output .= "FAILED QUERY: \n$query\n"; die($output);
				}
				$matchID = $wpdb->insert_id;
				$teamMatchIDs[$teamID][$i] = $matchID;
			}
			
			for($i=3; $i<=(count($teamRows)-1); $i++) {
				$playerName = esc_sql($teamRows[$i][0]);
				$query = "INSERT INTO `ss_players` (`team_id`, `name`) VALUES ('$teamID', '{$playerName}')";
				$output .= $query."\n";
				if(!$wpdb->query($query)) {
					$output .= "FAILED QUERY: \n$query\n"; die($output);
				}
				$playerID = $wpdb->insert_id;
				
				for($j=1; $j<=18; $j++) {
					$score=$teamRows[$i][$j];
					if(!empty($score)) {
						$legs=str_split($score);
						$leg1_column = ", `leg1_score`";
						$leg1_value = ", '{$legs[0]}'";
						$leg2_column = ", `leg2_score`";
						$leg2_value = ", '{$legs[1]}'";
						$leg3_column = ", `leg3_score`";
						$leg3_value = ", '{$legs[2]}'";

						$query = "INSERT INTO `ss_scores` (`match_id`, `player_id` $leg1_column $leg2_column $leg3_column ) VALUES ('{$teamMatchIDs[$teamID][$j]}', '$playerID' $leg1_value $leg2_value $leg3_value )";
						$output .= $query."\n";
						if(!$wpdb->query($query)) {
							$output .= "FAILED QUERY: \n$query\n"; die($output);
						}
						$scoreID = $wpdb->insert_id;
							
						$success .= "Successfully inserted Score ID: $scoreID, for player $playerID, playing in team $teamID, in match $matchID, in section $sectionID for week $j... Wow.\n";
					}
					
				}
				
			}
			
		}
	}
	
	echo $output;
	echo $success;
	
	die();
 }
 

	function findTeamFromNamePass1($pass1InputName, $pass1TeamsList) {
		$pass1InputName = preg_replace("|'.*'|si", '', $pass1InputName);
		$pass1InputName = preg_replace("|_|si", '', $pass1InputName);
		$foundTeam = false;
		foreach($pass1TeamsList as $team) {
			$testName = strtolower($team->name);
			if( levenshtein($testName, $pass1InputName) < 5 ) {
				// Difference between test name and input is less than 5 characters (to account for typos, spelling, 'A' etc)
				if($debugEcho) echo "found team using pass 1 levenshtein - team $pass1InputName is ".$team->name."\n\n";
				$foundTeam = $team;
			} elseif ( stripos($testName, $pass1InputName)!==false ) {
				// Input string is a substring in a team name, assume it's correct
				if($debugEcho) echo "found team using pass 1 substring - team $pass1InputName is ".$team->name."\n\n";
				$foundTeam = $team;
			}
		}
		if($foundTeam==false) {if($debugEcho) echo "could not find team using pass 1\n";}
		return $foundTeam;
	}

	function findTeamFromName($inputName, $teamsList) {
		$foundTeam = false;
		$inputName = strtolower(html_entity_decode($inputName));
		if($debugEcho) echo "inside findTeamFromName, input: $inputName\n";
		if($debugEcho) {echo "teamsList: "; foreach($teamsList as $team) {echo strtolower($team->name).", ";} }
		if($debugEcho) echo "\n";
		
		$nicknames = array('Stanford F.C'=>'STANFORD IN THE VALE F.C',
							'The Anchor'=>'OLD ANCHOR ABINGDON',
							'The Old Anchor'=>'OLD ANCHOR ABINGDON',
							'Pack Horse'=>'PACKHORSE MILTON',
							'Bowyer Arms'=>'BOWYERS ARMS RADLEY',
							'Hart of Harwell'=>'THE HART HARWELL',
							'Black Horse'=>'BLACK HORSE HANNEY',
							'Plough Hanney'=>'PLOUGH INN HANNEY',
							'The Hatchet'=>'THE HATCHET CHILDREY',
							'Eight Bells'=>'EIGHT BELLS EATON',
							'Crosss keys'=>'CROSS KEYS',
							'King Arms'=>'KINGS ARMS WANTAGE',
							'Black Swan Hanney'=>'BLACK SWAN ABINGDON',
							'Black Swan'=>'BLACK SWAN ABINGDON',
							'PLOUGH WITTENHAM'=>'PLOUGH LONG WITTENHAM',
							//'Drayton Gold Club'=>'DRAYTON PARK GOLF CLUB',
							//'Drayton Golf Park Club'=>'Drayton Park Golf Club',
							//'Drayton Golf Park'=>'Drayton Park Golf Club',
							//'Drayton Park Golf Club'=>'DRAYTON PARK VETS GOLF CLUB',
							//'Drayton Golf Club'=>'DRAYTON PARK VETS GOLF CLUB',
							//'DRAYTON PARK VETS CLUB'=>'DRAYTON PARK VETS GOLF CLUB',
							//'DRAYTON VETS'=>'DRAYTON PARK VETS GOLF CLUB',
							'Drayton Gold Club'=>'DRAYTON PARK',
							'Drayton Golf Park Club'=>'DRAYTON PARK',
							'Drayton Golf Park'=>'DRAYTON PARK',
							'Drayton Park Golf Club'=>'DRAYTON PARK',
							'Drayton Golf Club'=>'DRAYTON PARK',
							'DRAYTON PARK VETS CLUB'=>'DRAYTON PARK',
							'DRAYTON VETS'=>'DRAYTON PARK',
							'Red Lion A'=>'RED LION DRAYTON',
							'WOOTTON LEGION'=>'WOOTTON BRITISH LEGION',
							'GoodLake Arms'=>'GOOD LAKE ARMS CHALLOW',
							'hart of harwell'=>'HEART OF HARWELL',
							'Balck Horse'=>'black horse',
							'the bear'=>'bear',
							'Plugh Inn'=>'Plough Inn',
							'Abingdon Unied'=>'Abingdon United',
							'Abingodn United'=>'Abingdon United',
							'Cpread Eagle'=>'Spread Eagle',
							'Spread Egale'=>'Spread Eagle',
							'The Wheatsheaf'=>'Wheatsheaf',
							'Hind Head'=>'HINDS HEAD',
							'Horse & Harow'=>'HORSE & HARROW',
							'The Hatchett'=>'Hatchet',
						);
						
		$foundTeam = findTeamFromNamePass1($inputName, $teamsList);
		
		// If we're executing code here, we didn't find a match with our normal logic, so let's try nicknames 
		if($foundTeam == false) {
			if($debugEcho) echo "no team found after pass 1, checking nicknames\n";
			foreach($nicknames as $nick => $real) {
				
				if( levenshtein(strtolower($nick), $inputName) < 2 ) {
					$realName = strtolower($real);
					if($debugEcho) echo "inside levenshtein block, found nick! Real name is: $realName about to run pass 1: findTeamFromNamePass1( $realName, $teamsList ) \n";
					// Found our input in a nickname, recurse through original methods with replacement string
					$foundTeam = findTeamFromNamePass1($realName, $teamsList);
				}
			}
		}
			
		if($foundTeam==false) {if($debugEcho) echo "got to end of findTeamFromName function, both pass 1 and nicknames failed - returning false\n\n";}
		else {if($debugEcho) echo "successfully found team from name! input was '$inputName', correct name is '{$foundTeam->name}'\n\n";}
		return $foundTeam;
	}
 
	function getSeasonResults($year, $season, $debugEcho=false) {
		global $wpdb;
		
		if($season=="Summer") {
			$seasonTaxonomyID = 5;
			$dateQueryString = "`post_date` between DATE('$year-05-01') AND DATE('$year-09-15')";
		} else {
			$seasonTaxonomyID = 6;
			$dateQueryString = "`post_date` between DATE('$year-09-15') AND DATE('".($year+1)."-05-01')";
		}
		$getMatchReportsQuery = "SELECT * FROM `wp_posts`,`wp_term_relationships` WHERE `wp_posts`.`ID`=`object_id` AND `term_taxonomy_id`=$seasonTaxonomyID AND $dateQueryString ORDER BY ID DESC";
		if($debugEcho) echo $getMatchReportsQuery ;
		
		$matchReportsResults = $wpdb->get_results($getMatchReportsQuery);
		
		if($debugEcho) print_r($matchReportsResults);

		$seasonOutput = array();
		
		foreach($matchReportsResults as $postNum => $matchReportsResult) {
			$post = $matchReportsResult->post_content." ";
			
			$post = preg_replace('|<p>|si', '', $post);
			$post = preg_replace('|</p>|si', '', $post);
			$post = preg_replace('|<tr>|si', '', $post);
			$post = preg_replace('|</tr>|si', '', $post);
			$post = preg_replace('|<td>|si', '', $post);
			$post = preg_replace('|</td>|si', '', $post);
			$post = preg_replace('|</td>|si', '', $post);
			$post = preg_replace('|<tbody>|si', '', $post);
			$post = preg_replace('|</tbody>|si', '', $post);
			$post = preg_replace('|<table>|si', '', $post);
			$post = preg_replace('|</table>|si', '', $post);
			$post = preg_replace('|\n|si', '', $post);
			$post = preg_replace('| {2,900}|si', ' ', $post);
			$post = preg_replace('|^ +|si', '', $post);
			$post = preg_replace('|sixes.+|si', '', $post);
			$post = preg_replace('|.nbsp.|si', '', $post);
			$post = trim($post);

			$postOutput = array( 'successCount' => 0, 'errorCount' => 0, 'postTitle' => $matchReportsResult->post_title, 'postDate' => $matchReportsResult->post_date, 'postContent'=>$post, 'sections' => array() );
			if($debugEcho) echo "Post: $post \n\n";
			
			preg_match_all("|(.+?):(.+?)\. |si", $post, $sections);
			$sectionsResults = $sections[2];
			$sections = $sections[1];
			
			//print_r($sectionsResults); 
			
			foreach($sectionsResults as $sectionKey => $sectionResults) {
				// get section IDs teams should be in, to narrow down the guesswork
				if(stripos($sections[$sectionKey],'prem')!==false) {
					$premIDrow = $wpdb->get_row("SELECT * FROM `ss_sections` WHERE `name` = 'Premier' AND `year` = '$year' AND `season` = 'Summer'");
					$premID = $premIDrow->id;
					$aIDrow = $wpdb->get_row("SELECT * FROM `ss_sections` WHERE `name` = 'A' AND `year` = '$year' AND `season` = 'Summer'");
					$aID = $aIDrow->id;
					$sectionID = "$premID/$aID";
					$sectionQueryString = " WHERE `section_id` = '$premID' OR `section_id` = '$aID'";
				} else {
					$sectionName = preg_replace('|([A-Z]).*|si', '$1', trim($sections[$sectionKey]));
					$sectionIDrow = $wpdb->get_row("SELECT * FROM `ss_sections` WHERE `name` = '$sectionName' AND `year` = '$year' AND `season` = 'Summer'");
					$sectionID = $sectionIDrow->id;
					$sectionQueryString = " WHERE `section_id` = '$sectionID'";
				}
				
				// get team names from the correct section(s) so we can compare with the match team names and see if we have matches
				$teamsQuery = "SELECT id,name FROM `ss_teams` $sectionQueryString";
				$teams = $wpdb->get_results($teamsQuery);
				
				$matches = explode(',', $sectionResults);
				
				foreach($matches as $matchString) {
					preg_match_all("|(.+?)([0-9])(.+?)([0-9])|si", $matchString, $matchResults);
					$team1name = trim($matchResults[1][0]);
					$team1score = trim($matchResults[2][0]);
					$team2name = trim($matchResults[3][0]);
					$team2score = trim($matchResults[4][0]);
					$team1id = $team2id = 0;
					
					if($debugEcho) echo "starting findTeamFromName function, looking for team 1: $team1name using team list with query: $sectionQueryString\n";
					$team1 = findTeamFromName($team1name,$teams);
					if($team1!==false) {
						$team1id = $team1->id;
						$team1name = $team1->name;
					}
					
					if($debugEcho) echo "starting findTeamFromName function, looking for team 2: $team2name using team list with query: $sectionQueryString\n";
					$team2 = findTeamFromName($team2name,$teams);
					if($team2!==false) {
						$team2id = $team2->id;
						$team2name = $team2->name;
					}
					
					if($team1id != 0 AND $team2id != 0) {
						$postOutput['successCount']++;
						$postOutput['sections'][$sectionID][] = array( 'team1id'=>$team1id, 'team1name'=>$team1name, 'team1score'=>$team1score, 'team2id'=>$team2id, 'team2name'=>$team2name, 'team2score'=>$team2score );
					} elseif($team1id == 0 AND $team2id != 0) {
						$postOutput['errorCount']++;
						$postOutput['sections'][$sectionID][] = array( 'team1id'=>0, 'team1name'=>'-----> UNKNOWN <------', 'team1score'=>$team1score, 'team2id'=>$team2id, 'team2name'=>$team2name, 'team2score'=>$team2score );
					} elseif($team1id != 0 AND $team2id == 0) {
						$postOutput['errorCount']++;
						$postOutput['sections'][$sectionID][] = array( 'team1id'=>$team1id, 'team1name'=>$team1name, 'team1score'=>$team1score, 'team2id'=>0, 'team2name'=>'-----> UNKNOWN <------', 'team2score'=>$team2score );
					} else {
						$postOutput['errorCount']++;
						$postOutput['sections'][$sectionID][] = array( 'team1id'=>0, 'team1name'=>'-----> UNKNOWN <------', 'team1score'=>$team1score, 'team2id'=>0, 'team2name'=>'-----> UNKNOWN <------', 'team2score'=>$team2score );
					}
				}
			}
			
			$seasonOutput[$postNum] = $postOutput;
		}
		return $seasonOutput;
	}
 
 if(isset($_GET['parseseasonresults'])) {
	$year = $_GET['yea'];
	$season = $_GET['sea'];
	print_r(getSeasonResults($year,$season));
	die();
 }
 
 if(isset($_GET['processseasonresults'])) {
	$year = $_GET['yea'];
	$season = $_GET['sea'];
	$postNum = $_GET['num'];
	$week = $_GET['wea'];
	$go = $_GET['go'];

	$parsedResults = getSeasonResults($year, $season);

	$weekMatches = array();
	foreach($parsedResults[$postNum]['sections'] as $section) {
		foreach($section as $match) $weekMatches[]=$match; 
	}												

	foreach($weekMatches as $match) {
		$homeMatch = $wpdb->get_row("SELECT * FROM `ss_matches` WHERE `week_number` = '$week' AND `home_team_id` = '{$match['team1id']}' AND `home_team_points` = '{$match['team1score']}'");
		if($homeMatch != null) {
			$awayMatch = $wpdb->get_row("SELECT * FROM `ss_matches` WHERE `week_number` = '$week' AND `home_team_id` = '{$match['team2id']}' AND `home_team_points` = '{$match['team2score']}'");
			if($awayMatch != null) {
				$updateMatchQuery = "UPDATE `ss_matches` SET `away_team_id` = '{$match['team2id']}', `away_team_points` = '{$match['team2score']}' WHERE `id` = '{$homeMatch->id}'";
				$updateScoresQuery = "UPDATE `ss_scores` SET `match_id` = '{$homeMatch->id}' WHERE `match_id` = '{$awayMatch->id}'";
				$deleteMatchQuery = "DELETE FROM `ss_matches` WHERE `id` = '{$awayMatch->id}'";
				if(isset($go) && $go == 1) {
					if($wpdb->query($updateMatchQuery)) {
						if($wpdb->query($updateScoresQuery)) {
							if($wpdb->query($deleteMatchQuery)) {
								echo "Successfully updated match ID {$homeMatch->id} with away data\n";
							} else {
								echo "Fail on execute query: $deleteMatchQuery";
							}
						} else {
							echo "Fail on execute query: $updateScoresQuery";
						}
					}  else {
						echo "Fail on execute query: $updateMatchQuery";
					}
				} else {
					echo "Found match for both Home and Away teams with correct week and match. Queries to execute would be: \n";
					echo $updateMatchQuery."\n".$updateScoresQuery."\n".$deleteMatchQuery."\n\n";
				}
			} else {
				echo "Searching database for away team with correct week and points returned no results. Query: \n SELECT * FROM `ss_matches` WHERE `week_number` = '$week' AND `home_team_id` = '{$match['team2id']}' AND `home_team_points` = '{$match['team2score']}'\n\n";
			}
		} else {
			echo "Searching database for home team with correct week and points returned no results. Query: \n SELECT * FROM `ss_matches` WHERE `week_number` = '$week' AND `home_team_id` = '{$match['team1id']}' AND `home_team_points` = '{$match['team1score']}'\n\n";
		}
	}
	//print_r(getSeasonResults($year,$season));
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
                <article class="entry">
 
				<?	if(isset($_GET['automatchresults'])) {
						$year = $_GET['yea'];
						$season = $_GET['sea'];
						
						$sections = $wpdb->get_results("SELECT * FROM `ss_sections` WHERE `year` = '$year' AND `season` = '$season'");
						
						for($week=1; $week<=18; $week++) {
							echo "<h1>Week $week</h2>";
							
							foreach($sections as $section) {
								echo "<h2>$year $season - ".$section->name." Section</h2>";
								$teams = $wpdb->get_results("SELECT * FROM ss_teams WHERE `section_id` = {$section->id}");
								$teamNames = array();
								foreach($teams as $team) $teamNames[$team->id]=$team->name;
								$matches = $wpdb->get_results("SELECT * FROM ss_matches WHERE `home_team_id` IN (SELECT `id` FROM ss_teams WHERE `section_id` = {$section->id}) AND `week_number` = '$week'");
								
							?>	<table id="parsedMatches">
									<tr><th>Home Team</th><th>Score</th><th>Away Team</th><th>Score</th></tr>
								<?	foreach($matches as $match) { ?>
									<tr><td class="home_team_id"><?=$teamNames[$match->home_team_id]?></td><td class="home_team_points"><?=$match->home_team_points?></td><td class="away_team_id"><?=$teamNames[$match->away_team_id]?></td><td class="away_team_points"><?=$match->away_team_points?></td></tr>
								<? 	} ?>
									<!-- The player scores rows will be added here by ajax -->
								</table><table id="databaseMatches">
									<tr><th>Home Team</th><th>Score</th><th>Away Team</th><th>Score</th></tr>
								<?	foreach($matches as $match) { ?>
									<tr><td class="home_team_id"><?=$teamNames[$match->home_team_id]?></td><td class="home_team_points"><?=$match->home_team_points?></td><td class="away_team_id"><?=$teamNames[$match->away_team_id]?></td><td class="away_team_points"><?=$match->away_team_points?></td></tr>
								<? 	} ?>
								</table>
							<? } ?>
						<? } ?>
						
				<? 	} else { ?>
					This template should not be used - it is only for use by Andrew while developing the website.
				<?	} ?>
				
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
