<?php
/**
 * DataTables PHP libraries.
 *
 * PHP libraries for DataTables and DataTables Editor, utilising PHP 5.3+.
 *
 *  @author    SpryMedia
 *  @copyright 2012 SpryMedia ( http://sprymedia.co.uk )
 *  @license   http://editor.datatables.net/license DataTables Editor
 *  @link      http://editor.datatables.net
 */


//
// Error checking - check that we are PHP 5.3 or newer
//
if ( version_compare( PHP_VERSION, "5.3.0", '<' ) ) {
	echo json_encode( array(
		"sError" => "Editor PHP libraries required PHP 5.3 or newer. You are ".
			"currently using ".PHP_VERSION.". PHP 5.3 and newer have a lot of ".
			"great new features that the Editor libraries take advantage of to ".
			"present an easy to use and flexible API."
	) );
	exit(0);
}

function safe_json_encode($value){
	if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
		$encoded = json_encode($value, JSON_PRETTY_PRINT);
	} else {
		$encoded = json_encode($value);
	}
	switch (json_last_error()) {
		case JSON_ERROR_NONE:
			return $encoded;
		case JSON_ERROR_DEPTH:
			return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
		case JSON_ERROR_STATE_MISMATCH:
			return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
		case JSON_ERROR_CTRL_CHAR:
			return 'Unexpected control character found';
		case JSON_ERROR_SYNTAX:
			return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
		case JSON_ERROR_UTF8:
			$clean = utf8ize($value);
			return safe_json_encode($clean);
		default:
			return 'Unknown error'; // or trigger_error() or throw new Exception()

	}
}

function utf8ize($mixed) {
	if (is_array($mixed)) {
		foreach ($mixed as $key => $value) {
			$mixed[$key] = utf8ize($value);
		}
	} else if (is_string ($mixed)) {
		return utf8_encode($mixed);
	}
	return $mixed;
}

//
// Load the DataTables bootstrap core file and let it register the required 
// handlers.
//
include( dirname(__FILE__).'/Bootstrap.php' );

