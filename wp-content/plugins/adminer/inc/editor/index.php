<?php
/** Adminer Editor - Compact database editor
* @link https://www.adminer.org/
* @author Jakub Vrana, http://www.vrana.cz/
* @copyright 2009 Jakub Vrana
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

/*
 * Load WordPrass so that we can use
 * WordPress for access validation
 */

if ( file_exists( adminer_get_wp_load_path() . '/wp-load.php' ) ) {

	require_once( adminer_get_wp_load_path() . '/wp-load.php' );

} elseif ( file_exists( adminer_get_wp_root_path( dirname( dirname( __FILE__ ) ) ) . '/wp-load.php' ) ) {

	require_once( adminer_get_wp_root_path( dirname( dirname( __FILE__ ) ) ) . '/wp-load.php' );

} else {

	die( 'Cheatin&#8217; or you have the wrong path to <code>wp-load.php</code>, see the <a href="http://wordpress.org/extend/plugins/adminer/installation/">readme</a>?' );
	exit;

}


$current_user = wp_get_current_user();

if ( is_user_logged_in() && user_can( $current_user->ID, 'administrator' ) ) {

	adminer_load_editor();

} else {

	wp_die( __( 'Cheatin&#8217; uh?' ) );
	exit;
}

/**
 * Looking for the WordPress root path
 *
 * @return bool|string
 */
function adminer_get_wp_load_path() {

	$dir = dirname( __FILE__ );

	do {
		if ( file_exists( $dir . "/wp-load.php" ) ) {
			return $dir;
		}
	} while ( $dir = realpath( "$dir/.." ) );

	return FALSE;
}

// search and include wp-load.php
function adminer_get_wp_root_path( $directory ) {

	$wp_root = FALSE;
	foreach ( glob( $directory . "/*" ) as $f ) {

		if ( 'wp-load.php' == basename( $f ) ) {
			$wp_root = str_replace( "\\", "/", dirname( $f ) );

			return $wp_root;
		}

		if ( is_dir( $f ) ) {
			$newdir = dirname( dirname( $f ) );

			foreach ( glob( $f . "/*" ) as $subf ) {

				if ( 'wp-load.php' == basename( $subf ) ) {
					$wp_root = str_replace( "\\", "/", dirname( $subf ) );

					return $wp_root;
				}
			}
		}
	}

	if ( isset( $newdir ) && $newdir != $directory ) {
		if ( FALSE !== adminer_get_wp_root_path( $newdir ) ) {
			$wp_root = adminer_get_wp_root_path( $newdir );
		}
	}

	return $wp_root;
}

/**
 * Load the adminer Editior
 */
function adminer_load_editor(){

	include "../adminer/include/bootstrap.inc.php";
	$drivers[DRIVER] = lang('Login');

	if (isset($_GET["select"]) && ($_POST["edit"] || $_POST["clone"]) && !$_POST["save"]) {
		$_GET["edit"] = $_GET["select"];
	}

	if (isset($_GET["download"])) {
		include "../adminer/download.inc.php";
	} elseif (isset($_GET["edit"])) {
		include "../adminer/edit.inc.php";
	} elseif (isset($_GET["select"])) {
		include "../adminer/select.inc.php";
	} elseif (isset($_GET["script"])) {
		include "./script.inc.php";
	} else {
		include "./db.inc.php";
	}

	// each page calls its own page_header(), if the footer should not be called then the page exits
	page_footer();

}