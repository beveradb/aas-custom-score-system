<?php
/**
 * Header Template
 *
 * Here we setup all logic and XHTML that is required for the header section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>" />
<title><?php woo_title(); ?></title>
<?php woo_meta(); ?>
<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
<?php wp_head(); ?>
<?php woo_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php woo_top(); ?>
<div id="wrapper">    
<div id="fade" class="black_overlay"></div>

                            <div id="popupData" class="white_content" style="display:none; padding:20px; min-height:70%; height:auto;">
                                
                                           <table id="LeftContainerTable" >
                                        <tr>
                                            <td> 
                                                           <table id="homePlayerScoresPopup" class="playerScoresPopup">
                                        <tr class="scoresHeaderPopup">
                                            <th class="playersHeadingPopup">NAME</th>
                                            <th class="leg1Heading legHeadingPopup">1</th>
                                            <th class="leg2Heading legHeadingPopup">2</th>
                                            <th class="leg3Heading legHeadingPopup">3</th>
                                        </tr>
                                        <!-- The player scores rows will be added here by ajax -->
                                    </table>
                                            </td>
                                        </tr>
                                           <tr>
                                            <td> 
                                                
                                    <table id="homeTotalScoresPopup">
                                        <tr>
                                            <th class="totalsHeadingPopup">TOTALS</th>
                                            <th class="homeLeg1TotalPopup inputBoxPopup">0</th>
                                            <th class="homeLeg2TotalPopup inputBoxPopup">0</th>
                                            <th class="homeLeg3TotalPopup inputBoxPopup">0</th>
                                        </tr>
                                    </table>
                                            </td>
                                        </tr>
                                 </table>
                                           <table id="RightContainerTable" >
                                        <tr>
                                            <td> 
                                                     <table id="awayPlayerScoresPopup" class="playerScoresPopup">
                                        <tr class="scoresHeaderPopup">
                                            <th class="playersHeading">NAME</th>
                                            <th class="leg1Heading legHeadingPopup">1</th>
                                            <th class="leg2Heading legHeadingPopup">2</th>
                                            <th class="leg3Heading legHeadingPopup">3</th>
                                        </tr>
                                        <!-- The player scores rows will be added here by ajax -->
                                    </table>
                                            </td>
                                        </tr>
                                           <tr>
                                            <td> 
                                                         <table id="awayTotalScoresPopup">
                                        <tr>
                                            <th class="totalsHeadingPopup">TOTALS</th>
                                            <th class="awayLeg1TotalPopup inputBoxPopup">0</th>
                                            <th class="awayLeg2TotalPopup inputBoxPopup">0</th>
                                            <th class="awayLeg3TotalPopup inputBoxPopup">0</th>
                                        </tr>
                                    </table>
                                            </td>
                                        </tr>
                                 </table>
                     
                                
                                <div style="clear: both;">
                                  <input type="submit" class="submit button">
                                  <a class="close submit" href = "javascript:void(0)" onclick = "document.getElementById('popupData').style.display='none';document.getElementById('fade').style.display='none'">Back</a>
                                </div>
                            </div>



	<div id="inner-wrapper">

	<?php woo_header_before(); ?>

	<header id="header" class="col-full">

		<?php woo_header_inside(); ?>

	</header>
	<?php woo_header_after(); ?>