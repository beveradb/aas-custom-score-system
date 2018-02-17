<?php
/**
 * Template Name: Team Registration
 *
 * This page template shows the user the form to register their team for an Aunt Sally League
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
					
					<? if ( !is_user_logged_in() ) { 
						$errors = '';
						if ( isset( $_GET['wp-error'] ) )
						{
							$errors = strip_tags( $_GET['wp-error'] );
							$errors = str_ireplace( 'Lost your password?', '<a href="' . site_url( '/wp-login.php?action=lostpassword' ) . '">Lost your password?</a>', $errors );
							$errors = '<div class="pr-message pr-error"><p>' . $errors . '</p></div>';
						} ?>
						
						<p>You are required to login to view this page.</p>
						<form style="text-align: left;" action="<? echo get_bloginfo ( 'wpurl' )?>/wp-login.php" method="post">
							<p>
								<label for="log"><input type="text" name="log" id="log" value="<?=wp_specialchars ( stripslashes ( $user_login ) , 1 ) ?>" size="22" /> Username</label><br />
								<label for="pwd"><input type="password" name="pwd" id="pwd" size="22" /> Password</label><br />
								<input type="submit" name="submit" value="Log In" class="button" />
								<label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> Remember me</label><br />
							</p>
							<input type="hidden" name="redirect_to" value="<?=$_SERVER['REQUEST_URI']?>" />
						</form>
						<p>
							<a href="<? echo get_bloginfo ( 'wpurl' )?>/wp-register.php">Register</a> | <a href="<? echo get_bloginfo ( 'wpurl' )?>/wp-login.php?action=lostpassword">Lost your password?</a>
						</p>
						
						<? } else { ?>
								
							<?php if (have_posts()) : while (have_posts()) : the_post();?>
							<?php the_content(); ?>
							<?php endwhile; endif; ?>
						
							<?php if(isset($_POST['leagueSection'])) {
								$sectionID = intval( $_POST['leagueSection'] );
									if(!$sectionID ) $error = "Invalid section selected, please try again. <br /><br /><a href='?'>Back</a>";
								//$venueID = intval( $_POST['homeVenue'] );
									//if(!$venueID ) $error = "Invalid venue selected, please try again. <br /><br /><a href='?'>Back</a>"; 
								$teamName = sanitize_text_field( $_POST['teamName'] );
								$notes = sanitize_text_field( $_POST['notes'] );
								
								$teamData = array(
									'name' => $teamName,
									'section_id' => $sectionID,
									'notes' => $notes
								);
									//'venue_id' => $venueID,
								
								if(is_array($_POST['playerNames'])) {
									if( $wpdb->insert( "ss_teams", $teamData ) ) {
										$teamID = $wpdb->insert_id;
										echo "Inserted team with name: $teamName and ID: $teamID<br /><br />Players: ";
											$playerCounter = 0;
											foreach( $_POST['playerNames'] as $playerName ) {
												$playerData = array(
													'team_id' => $teamID,
													'name' => sanitize_text_field( $playerName )
												);
												
												if( $wpdb->insert( "ss_players", $playerData ) ) {
													$playerID = $wpdb->insert_id;
													echo "$playerName (ID: $playerID)";
													if(++$playerCounter === count($_POST['playerNames'])) {
														echo " were added to the players table and linked to the team.";
													} else echo ", ";
												} else $error = "Error while inserting player data, please try again. <br /><br /><a href='?'>Back</a>"; 
											}
									} else $error = "Error while inserting team data, please try again. <br /><br /><a href='?'>Back</a>"; 
								} else $error = "No players added, please try again. <br /><br /><a href='?'>Back</a>"; 
								
								if(strlen($error)>2) {
									echo $error;
								} else { 
									echo "<br /><br />Registration successful, thanks for entering! <br />
										Please keep an eye on your email for any updates and info.<br />
										<br /><a href='/'>Home</a>";
								}
							} else { ?>
						
							<div id="teamRegistration">
								<form action="?" method="POST">
								<table>
									<tr>
										<th class="leagueSectionHeading">LEAGUE SECTION</th>
										<td><select id="leagueSection" name="leagueSection">
											<? $sections = $wpdb->get_results("SELECT * FROM ss_sections WHERE open = '1'");
											foreach($sections as $section) { ?>
												<option value="<?=$section->id?>"><?=$section->season?> <?=$section->year?> - <?=$section->name?> Section</option>
											<? } ?>
										</select></td>
									</tr>
									
									<? /* <tr>
										<th class="homeVenueHeading">HOME VENUE</th>
										<td><select id="homeVenue" name="homeVenue">
											<? $venues = $wpdb->get_results("SELECT * FROM ss_venues");
											foreach($venues as $venue) { ?>
												<option value="<?=$venue->id?>"><?=$venue->name?></option>
											<? } ?>
										</select></td>
									</tr> */ ?>
									
									<tr>
										<th class="teamNameHeading">TEAM NAME</th>
										<td><input type="text" name="teamName"></input></td>
									</tr>
									
									<tr>
										<th class="playersHeading">PLAYERS</th>
										<td id="playersCell"><a href="#" id="addPlayerButton" class="button">ADD PLAYER</a></td>
									</tr>
									
									<tr>
										<th class="notesHeading">NOTES</th>
										<td><textarea name="notes"></textarea></td>
									</tr>
									
									<tr>
										<th colspan="2" class="submitForm"><input id="submitButton" type="submit" class="button"></input></th>
									</tr>
								</table>
								</form>
							</div>
							
							<script type="text/javascript">
								jQuery( "#addPlayerButton" ).click(function( event ) {
									event.preventDefault();
									var playerCount = jQuery('#addPlayerButton').data('playerCount');
									if(playerCount===undefined) {
										playerCount = 1;
									} else {
										playerCount++;
									}
									jQuery('#addPlayerButton').data('playerCount', playerCount);
									
									jQuery( "<div class='player'><span class='playerNumber'>Name:</span><input type='text' name='playerNames["+playerCount+"]'></input><a href='#' class='deletePlayerButton'>&#x2718;</a></div>" ).insertBefore( "#addPlayerButton" );
									
									jQuery( "a.deletePlayerButton" ).click(function( event ) {
										event.preventDefault();
										console.log(event.target);
										jQuery(event.target).closest( "div" ).remove();
									});
								});
							</script>
							
							<? } /* End block of code which is executed if no POST set */ ?>
							
						<? } /* End of logged-in restricted code */ ?>
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