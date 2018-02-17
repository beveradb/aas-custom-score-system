<?php

/*
 * Editor server script for DB table ss_venues
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
Editor::inst( $db, 'ss_venues' )
	->fields(
		Field::inst( 'name' )
			->validator( 'Validate::required' ),
		Field::inst( 'address' )
			->validator( 'Validate::required' ),
		Field::inst( 'phone' )
	)
	->process( $_POST )
	->json();
