<?php
/**
 * Template Name: Admin Functions
 *
 * This page template is only for developer usage (by Andrew!)
 *
 * @package WooFramework
 * @subpackage Template
 */
	define("DODEBUG",isset($_GET['debug']));
	define("DODEBUGQUERIES",isset($_GET['querydebug']));
	define("TABLEPREFIX",'ss_');
		
	function printdebug($identifier, $variable=false, $force=false) {
		// This function exists so we can easily dump variable data in an identifiable way to the screen whenever the URL parameter says we should
		// The idea is that while writing the code, any time you feel there is a variable which might be useful to dump when debugging, stick in a printdebug() call - it'll only show when the URL param is set
		if(DODEBUG OR $force) {
			if($variable) echo "----- START DEBUG: $identifier \n".print_r($variable,1)."\n----- END DEBUG: $identifier \n\n";
			else echo "----> DEBUG MESSAGE: $identifier \n";
		}
	}
	function querydebug($query, $identifier=false, $force=false) {
		// This function exists so we can easily dump mysql queries in an identifiable way to the screen instead of writing them to the DB, whenever the URL parameter says we should
		global $wpdb;
		if(DODEBUGQUERIES OR $force) {
			if($identifier) echo "----> DEBUG QUERY: $identifier ---> $query\n";
			else echo "----> DEBUG QUERY: $query\n";
			return true;
		} else {
			return $wpdb->query($query); 
		}
	}
 
	function simpleFindTeamName($inputName, $teamNames) {
		foreach($teamNames as $testName) {
			if( levenshtein($testName, $inputName) < 5 ) {
				// Difference between test name and input is less than 5 characters (to account for typos, spelling, 'A' etc)
				//printdebug("Found team using simple levenshtein - team $inputName is $testName");
				return $testName;
			} elseif ( stripos($testName, $inputName)!==false ) {
				// Input string is a substring in a team name, assume it's correct
				//printdebug("Found team using simple substring - team $inputName is $testName");
				return $testName;
			}
		}
		//printdebug("Could not find team for inputName $inputName using simple search");
		return false;
	}

	function correctTeamName($inputName, $sectionKey, $htmlSectionNames, $completeScoresData, $recurseSections=true) {
		// Get the text name of the section this team is supposed to be in
		$sectionName = $htmlSectionNames[$sectionKey+1];
		// Get the list of teams in this section from the HTML scores array
		$teamNames = array_keys($completeScoresData[$sectionName]);
		// If the section key we were given was 0, we assume it's the "Premier / A" section as far as the results post is concerned, so to get the list of possible teams we have to combine Premier and A (sections 0 and 1)
		if($sectionKey==0) $teamNames = array_merge($teamNames, array_keys($completeScoresData[$htmlSectionNames[0]]) );
		
		// Normalize input name a little bit - we're trying to do character matching, so everything has to be same case
		$inputName = strtoupper(html_entity_decode($inputName));
		$inputName = preg_replace("|'.*'|si", '', $inputName);
		$inputName = preg_replace("|_|si", '', $inputName);
		// Normalize list of teams
		$teamNames = array_map('strtoupper', $teamNames);
		$teamNames = array_map('html_entity_decode', $teamNames);
		//printdebug("Inside correctTeamName; Input name: $inputName, teamNames:", $teamNames);
		
		// This beautiful list is the list of... exceptions? Mostly typos, spelling mistakes and stupidity, it helps us find a match when there isn't an obvious match
		$nicknames = array(
			'STANFORD F.C'				=>	'STANFORD IN THE VALE F.C',
			'THE ANCHOR'				=>	'OLD ANCHOR ABINGDON',
			'THE OLD ANCHOR'			=>	'OLD ANCHOR ABINGDON',
			'PACK HORSE'				=>	'PACKHORSE MILTON',
			'BOWYER ARMS'				=>	'BOWYERS ARMS RADLEY',
			'HART OF HARWELL'			=>	'THE HART HARWELL',
			'BLACK HORSE'				=>	'BLACK HORSE HANNEY',
			'PLOUGH HANNEY'				=>	'PLOUGH INN HANNEY',
			'THE HATCHET'				=>	'THE HATCHET CHILDREY',
			'EIGHT BELLS'				=>	'EIGHT BELLS EATON',
			'CROSSS KEYS'				=>	'CROSS KEYS',
			'KING ARMS'					=>	'KINGS ARMS WANTAGE',
			'BLACK SWAN HANNEY'			=>	'BLACK SWAN ABINGDON',
			'BLACK SWAN'				=>	'BLACK SWAN ABINGDON',
			'PLOUGH WITTENHAM'			=>	'PLOUGH LONG WITTENHAM',
			'DRAYTON GOLD CLUB'			=>	'DRAYTON PARK',
			'DRAYTON GOLF PARK CLUB'	=>	'DRAYTON PARK',
			'DRAYTON GOLF PARK'			=>	'DRAYTON PARK',
			'DRAYTON PARK GOLF CLUB'	=>	'DRAYTON PARK',
			'DRAYTON GOLF CLUB'			=>	'DRAYTON PARK',
			'DRAYTON PARK VETS CLUB'	=>	'DRAYTON PARK',
			'DRAYTON VETS'				=>	'DRAYTON PARK',
			'RED LION A'				=>	'RED LION DRAYTON',
			'WOOTTON LEGION'			=>	'WOOTTON BRITISH LEGION',
			'GOODLAKE ARMS'				=>	'GOOD LAKE ARMS CHALLOW',
			'HART OF HARWELL'			=>	'HARWELL',
			'BALCK HORSE'				=>	'BLACK HORSE',
			'THE BEAR'					=>	'BEAR',
			'PLUGH INN'					=>	'PLOUGH INN',
			'ABINGDON UNIED'			=>	'ABINGDON UNITED',
			'ABINGODN UNITED'			=>	'ABINGDON UNITED',
			'CPREAD EAGLE'				=>	'SPREAD EAGLE',
			'SPREAD EGALE'				=>	'SPREAD EAGLE',
			'THE WHEATSHEAF'			=>	'WHEATSHEAF',
			'HIND HEAD'					=>	'HINDS HEAD',
			'HORSE & HAROW'				=>	'HORSE & HARROW',
			'THE HATCHETT'				=>	'HATCHET'
		);
		// Firstly, do a basic search of the input name in the teams list, using levenshtein (character difference, limit 5 chars) or substring (if input is a substring of any team in list, assume correct)
		$foundName = simpleFindTeamName($inputName, $teamNames);
		//printdebug("Found a correct team name for inputName $inputName, correct name is: $foundName");
		// We found a correct name! Return it.
		if($foundName) return $foundName;
		
		// If we got this far, we haven't found a match with our simple search, so let's try nickname replacements
		//printdebug("No team name found for inputName $inputName after simple search, checking nicknames");
		// Loop through nickname replacement array, if we find a match (fuzzy match by up to 2 chars), try a simple search with the replacement string instead
		foreach($nicknames as $nick => $nickReplacedName) {
			if( levenshtein($nick, $inputName) < 2 ) {
				//printdebug("Found nickname replacement for inputName $inputName, replacement is: $nickReplacedName, about to run simple search");
				$foundName = simpleFindTeamName($nickReplacedName, $teamNames);
				// We found a correct name! Return it.
				if($foundName) return $foundName;
			}
		}
		
		
		if($recurseSections) {
			$foundName = correctTeamName($inputName, $sectionKey-1, $htmlSectionNames, $completeScoresData, false); 
			if($foundName) return $foundName;
				
			$foundName = correctTeamName($inputName, $sectionKey+1, $htmlSectionNames, $completeScoresData, false); 
			if($foundName) return $foundName;
			
			printdebug("correctTeamName failed to find correct team for inputName $inputName in sectionKey $sectionKey. Teams list: ", $teamNames);
		}
		
		return false;
	}

	// THIS IF BLOCK IS FOR PARSING THE SCORES FROM THE HTML TABLES IN THE OLD ALL SCORES PAGES INTO THE DATABASE 
	// IT'S THEN GOING TO DO BADASS STUFF LIKE PARSE ALL THE OLD MATCH REPORTS AND CORRECT THE MATCH DATA WITH HOME/AWAY TEAMS, ETCETERA
	if(isset($_GET['importmatches'])) {
	for($year=2006; $year<=2013; $year++) {
		// Make the year a URL parameter, even though it's trivial to edit and reload...
		//$year = $_GET['yea'];
		// We only have scores data for summer leagues right now, so... summer it is.
		$season = 'Summer';
		// This is the ID in the database of the category which contains the written match reports 
		$seasonTaxonomyID = 5;
		// This date range should cover all match report posts for the summer league
		$dateQueryString = "`post_date` between DATE('$year-05-01') AND DATE('$year-09-15')";
		// IF WE EVER FIND WINTER SCORES DATA, USE THIS TO PARSE THE MATCH REPORT POSTS TOO:   if($season!="Summer") { $seasonTaxonomyID = 6; $dateQueryString = "`post_date` between DATE('$year-09-15') AND DATE('".($year+1)."-05-01')"; }
		// Create array to store all of the parsed data about this season from the match report blog posts, in a structured multi-dimensional array
		$completeReportsData = array();
		// Create array to store all the parsed data about this season from the all scores HTML page, in a structured multi-dimensional array
		$completeScoresData = array();
		// Create array to store the final, combined data we want to submit to the database
		$combinedData = array();
		// This file actually contains the scores HTML table. 2008-2013 are all the same format, not too hard to parse with a lot of regex
		$file = file_get_contents("/home/abingdon/public_html/oldresults/$year/all-scores.htm");
		// Apply fucktons of regex to get from a terrible HTML 4.01 table to a clean array of data
		$file = preg_replace('|.+?body>\s*\n*(.+)\s*\n*</body.+|si', '$1', $file);
		$file = preg_replace('|<br>|si', '', $file);
		$file = preg_replace('|<br />|si', '', $file);
		$file = preg_replace('|<hr>|si', '', $file);
		$file = preg_replace('|<hr />|si', '', $file);
		$file = preg_replace('|<table.*?>|si', '~TEAMSTART~', $file);
		$file = preg_replace('|</table>|si', '~TEAMEND~', $file);
		$file = preg_replace('|\s*\n*<h2>(.+?) Section</h2>|si', "\n~SECTIONEND~\n~SECTIONNAME~$1~", $file);
		$file = preg_replace('| align=.*?>|si', '>', $file);
		$file = preg_replace('| colspan=.*?>|si', '>', $file);
		$file = preg_replace('| width=.*?>|si', '>', $file);
		$file = preg_replace('|&nbsp;|si', '', $file);
		$file = preg_replace('|>-<|si', '><', $file);
		$file = preg_replace('|<b>|si', '', $file);
		$file = preg_replace('|</b>|si', '', $file);
		$file = preg_replace('|<i>|si', '', $file);
		$file = preg_replace('|</i>|si', '', $file);
		$file = preg_replace('|<blockquote>|si', '', $file);
		$file = preg_replace('|</blockquote>|si', '', $file);
		$file = preg_replace('|Total Points: ([0-9]+)|si', '$1', $file);
		$file = preg_replace('|<tbody>|si', '', $file);
		$file = preg_replace('|</tbody>|si', '', $file);
		$file = preg_replace('|</td>\s*\n*|si', ',', $file);
		$file = preg_replace('|<td>|si', '', $file);
		$file = preg_replace('|<td>|si', '', $file);
		$file = preg_replace('|\s*\n*<tr>\s*\n*|si', "\n", $file);
		$file = preg_replace('|\s*\n*</tr>\s*\n*|si', "\n", $file);
		$file = preg_replace('|^\n~SECTIONEND~\n(.+)\n$|si', "$1\n~SECTIONEND~", $file);
		
		//printdebug($file); die();
		
		preg_match_all("|~SECTIONNAME~(.+?)~\n(.+?)\n~SECTIONEND~|si", $file, $htmlSectionsMatches);
		//printdebug($htmlSectionsMatches); die();
		
		// This is the array of section names as found in the html table
		$htmlSectionNames = $htmlSectionsMatches[1];
		printdebug("htmlSectionNames", $htmlSectionNames);
		// Each string in this array contains all the teams, players, scores for each week, etc, delimited by TEAMSTART/TEAMEND and commas between lines
		$htmlSectionStrings = $htmlSectionsMatches[2];
		printdebug("htmlSectionStrings", $htmlSectionStrings);
		// Loop through all sections found, parse string to get team names and data, then parse csv data into array
		foreach($htmlSectionNames as $key => $sectionName) {
			// Get individual teams data out of section string into array
			preg_match_all("|~TEAMSTART~\n(.+?)\n~TEAMEND~|si", $htmlSectionStrings[$key], $teams);
			// Loop through each team string 
			foreach($teams[1] as $teamString) {
				// Loop through each line in team string
				$teamLines = explode("\n",$teamString);
				$lineData = array();
				foreach($teamLines as $key => $teamLine) {
					// Add csv parsed data (basically a player name and all scores for that player) to array
					$lineData[$key] = str_getcsv($teamLine);
				}
				// Add the array of player scores for this team to the final data array, nested by section then team name (assuming team name is the first cell in the first row of the table)
				$completeScoresData[$sectionName][$lineData[0][0]] = $lineData;
			}

		}

		/* SO NOW WE HAVE PARSED THE HTML scores page, we have an array which contains all the known sections, all the known teams in each section, the team points for each week, and an array of players and week scores */
		printdebug('completeScoresData', $completeScoresData);
		/* Let's start processing the match report blog posts now! */
		
		// Query the DB for all the match report posts for this year/season  
		$getMatchReportsQuery = "SELECT * FROM `wp_posts`,`wp_term_relationships` WHERE `wp_posts`.`ID`=`object_id` AND `term_taxonomy_id`=$seasonTaxonomyID AND $dateQueryString ORDER BY ID DESC";
		$matchReportsResults = $wpdb->get_results($getMatchReportsQuery);
		//printdebug("getMatchReportsQuery", $getMatchReportsQuery);
		//printdebug("matchReportsResults", $matchReportsResults);
		
		// Loop through posts from DB (hopefully 18 of them!), clean up content massively with a bunch of regex
		foreach($matchReportsResults as $postNum => $matchReportsResult) {
			// Super regex, strip all tags and whitespace, fix a couple of formatting fails
			$post = preg_replace('|<p>|si', '', $matchReportsResult->post_content." ");
			$post = preg_replace('|</p>|si', '', $post);
			$post = preg_replace('|<tr>|si', '', $post);
			$post = preg_replace('|</tr>|si', '', $post);
			$post = preg_replace('|<td>|si', '', $post);
			$post = preg_replace('|</td>|si', '', $post);
			$post = preg_replace('|</td>|si', '', $post);
			$post = preg_replace('|<tbody>|si', '', $post);
			$post = preg_replace('|</tbody>|si', '', $post);
			$post = preg_replace('|<blockquote>|si', '', $post);
			$post = preg_replace('|</blockquote>|si', '', $post);
			$post = preg_replace('|<table>|si', '', $post);
			$post = preg_replace('|</table>|si', '', $post);
			$post = preg_replace('|\n|si', '', $post);
			$post = preg_replace('| {2,900}|si', ' ', $post);
			$post = preg_replace('|^ +|si', '', $post);
			$post = preg_replace('|sixes.+|si', '', $post);
			$post = preg_replace('|.nbsp.|si', '', $post);
			$post = trim($post).' ';
			// Add entry to output array for this post, with counters, post metadata and array of sections (which will consequently be updated to contain all the known matches)  
			$completeReportsData[$postNum] = array( 'successCount' => 0, 'errorCount' => 0, 'postTitle' => $matchReportsResult->post_title, 'postDate' => $matchReportsResult->post_date, 'postContent'=>$post, 'sections' => array() );
			// Print cleaned up post content if debugging, since it helps massively
			printdebug("Post $postNum", $post);
			// Match all sections in post (assuming sections are suffixed by a colon), and the match results within each section (assuming each section results string ends in a dot)
			preg_match_all("|(.+?):(.+?)\. |si", $post, $postSectionsMatches);
			// This is the array of section names
			$postSectionNames = $postSectionsMatches[1];
			printdebug("postSectionNames", $postSectionNames);
			// This is the array of strings which actually contain the match results
			$postSectionStrings = $postSectionsMatches[2];			
			printdebug("postSectionStrings", $postSectionStrings);
			
			/* Loop through section strings in report post, split into matches with home/away teams and scores, correct team names by looking in data from HTML table, then add match data to combined array */  
			foreach($postSectionStrings as $sectionKey => $sectionResults) {
				// Split string into array of matches, assuming there is a comma between each match
				$sectionMatchStrings = explode(',', $sectionResults);
				// Loop through each match string, clean up and correct team names (hardest bit!)
				foreach($sectionMatchStrings as $matchString) {
					// Get match string as array, assuming format HOMETEAMNAME HOMETEAMSCORE AWAYTEAMNAME AWAYTEAMSCORE. Get rid of whitespace at the same time using \s
					preg_match_all("|\s*(.+?)\s*([0-9])\s*(.+?)\s*([0-9])\s*|si", $matchString, $matchResults);
					$homeName = $matchResults[1][0];
					$homeScore = $matchResults[2][0];
					$awayName = $matchResults[3][0];
					$awayScore = $matchResults[4][0];
					// Send written team name and section number to external function to check against all the possible team names in the HTML table data array and find the most likely candidate for a match using lots of trickery 
					$homeName = correctTeamName($homeName, $sectionKey, $htmlSectionNames, $completeScoresData);
					$awayName = correctTeamName($awayName, $sectionKey, $htmlSectionNames, $completeScoresData);
					printdebug("Original homeName: {$matchResults[1][0]}, Corrected homeName: $homeName. Original awayName: {$matchResults[3][0]}, Corrected awayName: $awayName.");
					
					if($homeName == false OR $awayName == false)
						$completeReportsData[$postNum]['errorCount']++;
					else
						$completeReportsData[$postNum]['successCount']++;
					
					// Add match details to complete reports data array even if we didn't manage to get one of the team names - this way we can possibly deduce the team name or score from remaining scores after successful matches are in place
					$completeReportsData[$postNum]['sections'][$sectionKey][] = array( "homeName"=>$homeName, "homeScore"=>$homeScore, "awayName"=>$awayName, "awayScore"=>$awayScore );
				}
			}
		}
		
		/* SO NOW WE HAVE PARSED ALL OF THE MATCH REPORT POSTS into an array which contains post number, basic metadata about the post, a "sections" array with all the matches in each section with corrected names */
		printdebug("completeReportsData", $completeReportsData);
		
		printdebug("completeScoresData", $completeScoresData);
		
		// Refactor completeScoresData with no change to the data, only simplification
		$completeScoresDataSimple = array();
		for($weekNum=1; $weekNum<=18; $weekNum++) {
			$completeScoresDataSimple[$weekNum] = array();
			foreach($completeScoresData as $section => $teams) {
				foreach($teams as $teamName => $teamData) {
					$completeScoresDataSimple[$weekNum][$section]['matchesWithOnlyOneTeam'][$teamName] = $teamData[1][$weekNum];
				}
			}
		}
		printdebug("completeScoresDataSimple", $completeScoresDataSimple );
		
		
		// Combine Premier and A sections into one section for each week
		$completeScoresDataSimplePremA = array();
		foreach( $completeScoresDataSimple as $weekNum => $weekResults ) {
			$completeScoresDataSimplePremA[$weekNum] = array();
			$premResults = array();
			foreach( $weekResults as $weekSectionName => $weekSectionResults ) {
				$weekSectionResults = $weekSectionResults['matchesWithOnlyOneTeam'];
				if(stripos($weekSectionName, 'rem')!==false) {
					$premResults = $weekSectionResults;
				} elseif(stripos($weekSectionName, 'a')!==false) {
					$completeScoresDataSimplePremA[$weekNum][0]['matchesWithOnlyOneTeam'] = array_merge($premResults, $weekSectionResults);
				} else {
					$completeScoresDataSimplePremA[$weekNum][array_search($weekSectionName,array_keys($weekResults))-1]['matchesWithOnlyOneTeam'] = $weekSectionResults;
				}
			}
		}
		printdebug("completeScoresDataSimplePremA", $completeScoresDataSimplePremA);
		
		// Copy scores array to new array so we can unset results we don't want any more. This way we keep all scores data which isn't mentioned by the match reports, while cleaning up stuff we can improve
		$combinedScoresData = $completeScoresDataSimplePremA;
		$successfulMatchCounter = 0;
		$correctedMatchCounter = 0;
		// Loop through reports data array and match up teams and scores in each match with data from simple scores array
		foreach( $completeReportsData as $weekNum => $weekReport ) {
			// Increment weekNum since in the reports array they are 0-indexed 
			$weekNum++;
			foreach( $weekReport['sections'] as $sectionNum => $weekSectionMatches ) {
				$combinedScoresData[$weekNum][$sectionNum]['matches'] = array();
				foreach( $weekSectionMatches as $match ) {
					// Both teams in this match exist in our scores array, let's compare scores and add the results to 
					if( array_key_exists($match['homeName'], $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam']) AND array_key_exists($match['awayName'],$combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam']) ) {						
						
						printdebug("Found a week $weekNum, section $sectionNum match report with matching team names: ", $match);
						printdebug("Here is the homeName in the onlyoneteam array which are relevant: ", $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam'][$match['homeName']]);
						printdebug("Here is the awayName in the onlyoneteam array which are relevant: ", $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam'][$match['homeName']]);
						
						$scoresDataHomeScore = $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam'][$match['homeName']];
						$scoresDataAwayScore = $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam'][$match['awayName']];
						
						if($match['homeScore'] == $scoresDataHomeScore AND $match['awayScore'] == $scoresDataAwayScore) { 
							$combinedScoresData[$weekNum][$sectionNum]['matches'][] = array( 
																						'homeName' => $match['homeName'],
																						'homeScore' => $match['homeScore'],
																						'awayName' => $match['awayName'],
																						'awayScore' => $match['awayScore'],
																						'importMessage' => 'Success'
																					);
							$successfulMatchCounter++;
						} else {
							$importMessage = "All Scores table did not match report. TableDataHome: $scoresDataHomeScore, TableDataAway: $scoresDataAwayScore. ReportDataHome: {$match['homeScore']}, ReportDataAway: {$match['awayScore']}. \n ";
							if(!empty($scoresDataHomeScore) AND !empty($scoresDataAwayScore)) {
								if( $scoresDataHomeScore+$scoresDataAwayScore == 6 ) {
									$importMessage .= "Error: TABLESCORES. Trusting table data, as it adds up to 6. "; 
								} elseif( $match['homeScore']+$match['awayScore'] ) {
									$importMessage .= "Error: REPORTSCORES. Trusting report data, as it adds up to 6 so seems more likely to be correct. ";
									$scoresDataHomeScore = $match['homeScore'];
									$scoresDataAwayScore = $match['awayScore'];
									$correctedMatchCounter++;
								} else {
									$importMessage .= "Error: TABLESCORESNONSENSE. Neither table or report data scores add up to 6, using table data as more trustable. "; 
								}
							}
							if($scoresDataHomeScore==='') {
								$scoresDataHomeScore = $match['homeScore'];
								$importMessage .= 'Error: HOMESCOREFROMREPORT. All Scores table had no value for home, using report value. ';
							}
							if($scoresDataAwayScore==='') {
								$scoresDataAwayScore = $match['awayScore'];
								$importMessage .= 'Error: AWAYSCOREFROMREPORT. All Scores table had no value for away, using report value. ';
							}
							
							$combinedScoresData[$weekNum][$sectionNum]['matches'][] = array(
																						'homeName' => $match['homeName'],
																						'homeScore' => $scoresDataHomeScore,
																						'awayName' => $match['awayName'],
																						'awayScore' => $scoresDataAwayScore,
																						'importMessage' => $importMessage
																					);
						}
						
						unset( $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam'][$match['homeName']] );
						unset( $combinedScoresData[$weekNum][$sectionNum]['matchesWithOnlyOneTeam'][$match['awayName']] );
					}
				}
			}
		}
		
		printdebug("combinedScoresData", $combinedScoresData);
		
		/* SOOO We're done parsing all the data and combining it into nice pretty arrays with CORRECTED MATCHES. Let's insert all this awesome stuff into the database. */
		
		// This keeps track of the DB ID of a "Premier" or a "C" etc.
		$sectionNameIDs = array();
		// This keeps track of which section names are in which numerical section, so we can easily translate to the combined array keys
		$sectionNameNumbers = array();
		// This array keeps track of the team IDs in each section. The sections are in the numerical format, meaning 'Premier' and 'A' are both 0 in here. 
		$numericalSectionNameTeamIDs = array();
		$numericalSectionNameCounter = 0;
		// This lookup array allows us to keep track of which team was playing in which match on which week
		$teamWeekMatchIDs = array();
		
		// First, we assume the all scores table list of sections and teams in each section should be valid, and create sections and teams in DB
		foreach($completeScoresData as $sectionName => $teams) {
			$query = "INSERT INTO `".TABLEPREFIX."sections` (`name`, `year`, `season`, `open`) VALUES ('$sectionName', '$year', 'Summer', '0')";
			if(!querydebug($query)) {
				printdebug("Failed Query while inserting section '$sectionName' with season Summer and year $year", $query, 1); die();
			}
			$sectionNameIDs[$sectionName] = $sectionID = $wpdb->insert_id;
			if(stripos($sectionName,'rem')===false AND stripos($sectionName,'A')===false)
				$numericalSectionNameCounter++;
			$sectionNameNumbers[$sectionName] = $numericalSectionNameCounter;

			foreach($teams as $teamName => $teamRows) {
				$teamName = esc_sql($teamName);
				$query = "INSERT INTO `".TABLEPREFIX."teams` (`name`, `section_id`) VALUES ('$teamName', '$sectionID')";
				if(!querydebug($query)) {
					printdebug("Failed Query while inserting team to section '$sectionName' with sectionID: $sectionID", $query, 1); die();
				}
				$numericalSectionNameTeamIDs[$numericalSectionNameCounter][stripslashes($teamName)] = $wpdb->insert_id;
			}
		}
		printdebug('numericalSectionNameTeamIDs', $numericalSectionNameTeamIDs);

		// Now we can loop through our combined match data array and insert matches
		foreach( $combinedScoresData as $weekNum => $weekSections ) {
			foreach ( $weekSections as $weekSectionNum => $weekSectionMatchData ) {
				foreach ( $weekSectionMatchData['matches'] as $matchData ) {
					$homeTeamID = $numericalSectionNameTeamIDs[$weekSectionNum][$matchData['homeName']];
					$awayTeamID = $numericalSectionNameTeamIDs[$weekSectionNum][$matchData['awayName']];
					printdebug("inside loop inserting match, homeTeamID is $homeTeamID, awayTeamID is $awayTeamID ");
					
					$query = "INSERT INTO `".TABLEPREFIX."matches` (`home_team_id`, `home_team_points`, `away_team_id`, `away_team_points`, `week_number`, `import_message`) VALUES ('$homeTeamID', '{$matchData['homeScore']}', '$awayTeamID', '{$matchData['awayScore']}', '$weekNum', '{$matchData['importMessage']}')";
					if(!querydebug($query)) {
						printdebug("Failed Query while inserting week $weekNum match in numerical (prem/a combined) section num $weekSectionNum", $query, 1); die();
					}
					$teamWeekMatchIDs[$homeTeamID][$weekNum] = $matchID = $wpdb->insert_id;
					$teamWeekMatchIDs[$awayTeamID][$weekNum] = $matchID;
				}
				
				foreach ( $weekSectionMatchData['matchesWithOnlyOneTeam'] as $teamName => $teamScore ) {
					$homeTeamID = $numericalSectionNameTeamIDs[$weekSectionNum][$teamName];
					$importMessage = "Error: NOMATCH. Could not find this match in report. Only data from all scores table shown, assumed home team. ";
					$query = "INSERT INTO `".TABLEPREFIX."matches` (`home_team_id`, `home_team_points`, `week_number`, `import_message`) VALUES ('$homeTeamID', '$teamScore', '$weekNum', '$importMessage')";
					if(!querydebug($query)) {
						printdebug("Failed Query while inserting week $weekNum match in numerical (prem/a combined) section num $weekSectionNum", $query, 1); die();
					}
					$teamWeekMatchIDs[$homeTeamID][$weekNum] = $matchID = $wpdb->insert_id;
				}
			}
		}

		printdebug('teamWeekMatchIDs', $teamWeekMatchIDs); 
		
		// Now we have inserted sections, teams and matches into the DB successfully, let's get the players and leg scores out of the all scores table
		foreach($completeScoresData as $sectionName => $teams) {
			foreach($teams as $teamName => $teamRows) {
				$teamID = $numericalSectionNameTeamIDs[$sectionNameNumbers[$sectionName]][$teamName];
				for($allScoresTeamTableRow=3; $allScoresTeamTableRow<=(count($teamRows)-1); $allScoresTeamTableRow++) {
					$playerName = esc_sql($teamRows[$allScoresTeamTableRow][0]);
					$query = "INSERT INTO `".TABLEPREFIX."players` (`team_id`, `name`) VALUES ('$teamID', '{$playerName}')";
					if(!querydebug($query)) {
						printdebug("Failed Query while inserting section '$sectionName', team '$teamName', teamID $teamID player", $query, 1); die();
					}
					$playerID = $wpdb->insert_id;
					
					for($weekNum=1; $weekNum<=18; $weekNum++) {
						$score=$teamRows[$allScoresTeamTableRow][$weekNum];
						if(!empty($score)) {
							$legs=str_split($score);
							$leg1_column = ", `leg1_score`";
							$leg1_value = ", '{$legs[0]}'";
							$leg2_column = ", `leg2_score`";
							$leg2_value = ", '{$legs[1]}'";
							$leg3_column = ", `leg3_score`";
							$leg3_value = ", '{$legs[2]}'";

							$query = "INSERT INTO `".TABLEPREFIX."scores` (`match_id`, `player_id` $leg1_column $leg2_column $leg3_column ) VALUES ('{$teamWeekMatchIDs[$teamID][$weekNum]}', '$playerID' $leg1_value $leg2_value $leg3_value )";
							if(!querydebug($query)) {
								printdebug("Failed Query while inserting player ID $playerID, match ID ".$teamWeekMatchIDs[$teamID][$weekNum], $query, 1); die();
							}
							$scoreID = $wpdb->insert_id;
								
							printdebug("Successfully inserted Score ID: $scoreID, for player $playerID, playing in team $teamID, in match {$teamWeekMatchIDs[$teamID][$weekNum]}, in section $sectionName for week $weekNum",false,1);
						}
						
					}
					
				}
				
			}
		}
		
	}
	}
	die();
 
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
						
						$sections = $wpdb->get_results("SELECT * FROM `".TABLEPREFIX."sections` WHERE `year` = '$year' AND `season` = '$season'");
						
						for($week=1; $week<=18; $week++) {
							echo "<h1>Week $week</h2>";
							
							foreach($sections as $section) {
								echo "<h2>$year $season - ".$section->name." Section</h2>";
								$teams = $wpdb->get_results("SELECT * FROM ".TABLEPREFIX."teams WHERE `section_id` = {$section->id}");
								$teamNames = array();
								foreach($teams as $team) $teamNames[$team->id]=$team->name;
								$matches = $wpdb->get_results("SELECT * FROM ".TABLEPREFIX."matches WHERE `home_team_id` IN (SELECT `id` FROM ".TABLEPREFIX."teams WHERE `section_id` = {$section->id}) AND `week_number` = '$week'");
								
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