
/*
 * Editor client script for DB table ss_sections
 * Automatically generated by http://editor.datatables.net/generator
 */

(function($){

$(document).ready(function() {
    jQuery('#updateWeekNumber').off().click(function(){
        
        jQuery.post( window.location.href, { weekNumber: jQuery('#weekNumber').val() })
        .done(function( data ) {
            location.reload();
        });
    });
    
	var ss_sections_editor = new $.fn.dataTable.Editor( {
		"ajaxUrl": "/wp-content/themes/canvas/includes/php/table.ss_sections.php",
		"domTable": "#ss_sections",
		"fields": [
			{
				"label": "Year",
				"name": "year",
				"type": "text",
				"default": (new Date).getFullYear()
			},
			{
				"label": "League Name / Season",
				"name": "season",
				"type": "text"
			},
			{
				"label": "Section Name",
				"name": "name",
				"type": "text"
			},
			{
				"label": "Registration",
				"name": "open",
				"type": "select",
				"ipOpts": [
					{
						"label": "Closed",
						"value": "0"
					},
					{
						"label": "Open",
						"value": "1"
					}
				]
			}
		]
	} );

	$('#ss_sections').dataTable( {
		"sDom": 'TC<"clear">Rlfrtip',
		"sAjaxSource": "/wp-content/themes/canvas/includes/php/table.ss_sections.php",
		"aoColumns": [
			{
				"mData": "year"
			},
			{
				"mData": "season"
			},
			{
				"mData": "name"
			},
			{
				"mData": "open",
				"mRender": function ( data, type, full ) {
					if(data == 0) { return "Closed"; }
					if(data == 1) { return "Open"; }
				}
			}
		],
		"aaSorting": [[ 3, "desc" ],[ 0, "desc" ],[ 1, "desc" ]],
		"oTableTools": {
			"sSwfPath": "/wp-content/themes/canvas/includes/js/copy_csv_xls_pdf.swf",
			"sRowSelect": "multi",
			"aButtons": [
				{ "sExtends": "editor_create", "editor": ss_sections_editor },
				{ "sExtends": "editor_edit",   "editor": ss_sections_editor },
				{ "sExtends": "editor_remove", "editor": ss_sections_editor },
				"select_all",
				"select_none",
                {
                    "sExtends":    "collection",
                    "sButtonText": "Export",
                    "aButtons":    [ "copy", "print", "csv", "xls", "pdf" ]
                }
			]
		}
	} );
	
	var ss_venues_editor = new $.fn.dataTable.Editor( {
		"ajaxUrl": "/wp-content/themes/canvas/includes/php/table.ss_venues.php",
		"domTable": "#ss_venues",
		"fields": [
			{
				"label": "Name",
				"name": "name",
				"type": "text"
			},
			{
				"label": "Address",
				"name": "address",
				"type": "text"
			},
			{
				"label": "Phone",
				"name": "phone",
				"type": "text"
			}
		]
	} );

	$('#ss_venues').dataTable( {
		"sDom": 'TC<"clear">Rlfrtip',
		"sAjaxSource": "/wp-content/themes/canvas/includes/php/table.ss_venues.php",
		"aoColumns": [
			{
				"mData": "name"
			},
			{
				"mData": "address"
			},
			{
				"mData": "phone"
			}
		],
		"oTableTools": {
			"sSwfPath": "/wp-content/themes/canvas/includes/js/copy_csv_xls_pdf.swf",
			"sRowSelect": "multi",
			"aButtons": [
				{ "sExtends": "editor_create", "editor": ss_venues_editor },
				{ "sExtends": "editor_edit",   "editor": ss_venues_editor },
				{ "sExtends": "editor_remove", "editor": ss_venues_editor },
				"select_all",
				"select_none",
                {
                    "sExtends":    "collection",
                    "sButtonText": "Export",
                    "aButtons":    [ "copy", "print", "csv", "xls", "pdf" ]
                }
			]
		}
	} );
	
	var ss_teams_editor = new $.fn.dataTable.Editor( {
		"ajaxUrl": "/wp-content/themes/canvas/includes/php/table.ss_teams.php",
		"domTable": "#ss_teams",
		"fields": [
			{
				"label": "Section",
				"name": "section_id",
				"type": "select"
			},
			{
				"label": "Name",
				"name": "name",
				"type": "text"
			},
			{
				"label": "Notes",
				"name": "notes",
				"type": "text"
			}
		]
	} );

	$('#ss_teams').dataTable( {
		"sDom": 'TC<"clear">Rlfrtip',
		"sAjaxSource": "/wp-content/themes/canvas/includes/php/table.ss_teams.php",
		"aoColumns": [
			{
				"mData": "section_name",
				"sWidth": "20%"
			},
			{
				"mData": "name",
				"sWidth": "25%"
			},
			{
				"mData": "notes"
			},
			{
				"mData": "DT_RowId",
				"mRender": function (data, type, full) {
				  return '<a href="?page=scoreSystemManager-dashboard&editteam='+data.substr(4)+'">View / Edit</a>';
				},
				"sWidth": "10%"
			},
		],
		"aaSorting": [[ 0, "desc" ]],
		"oTableTools": {
			"sSwfPath": "/wp-content/themes/canvas/includes/js/copy_csv_xls_pdf.swf",
			"sRowSelect": "multi",
			"aButtons": [
				{ "sExtends": "editor_create", "editor": ss_teams_editor },
				{ "sExtends": "editor_edit",   "editor": ss_teams_editor },
				{ "sExtends": "editor_remove", "editor": ss_teams_editor },
				"select_all",
				"select_none",
                {
                    "sExtends":    "collection",
                    "sButtonText": "Export",
                    "aButtons":    [ "copy", "print", "csv", "xls", "pdf" ]
                }
			]
		},
		"fnInitComplete": function ( settings, json ) {
			ss_teams_editor.field('section_id').update( json.sectionData );
		}
	} );
	
	var ss_players_editor = new $.fn.dataTable.Editor( {
		"ajaxUrl": "/wp-content/themes/canvas/includes/php/table.ss_players.php?teamID="+teamID,
		"domTable": "#ss_players",
		"fields": [
			{
				"label": "Team",
				"name": "team_id",
				"type": "select"
			},
			{
				"label": "Name",
				"name": "name",
				"type": "text"
			}
		],
		"events": {
			"onCreate": function (json, data) {
			},
			"onEdit": function (json, data) {
			},
			"onOpen": function ( settings, json ) {
				$('#DTE_Field_team_id').val(teamID);
			}
		}
	} );

	$('#ss_players').dataTable( {
		"sDom": 'TC<"clear">Rlfrtip',
		"sAjaxSource": "/wp-content/themes/canvas/includes/php/table.ss_players.php?teamID="+teamID,
		"aoColumns": [
			{
				"mData": "team_name"
			},
			{
				"mData": "name"
			}
		],
		"oTableTools": {
			"sSwfPath": "/wp-content/themes/canvas/includes/js/copy_csv_xls_pdf.swf",
			"sRowSelect": "multi",
			"aButtons": [
				{ "sExtends": "editor_create", "editor": ss_players_editor },
				{ "sExtends": "editor_edit",   "editor": ss_players_editor },
				{ "sExtends": "editor_remove", "editor": ss_players_editor },
				"select_all",
				"select_none",
                {
                    "sExtends":    "collection",
                    "sButtonText": "Export",
                    "aButtons":    [ "copy", "print", "csv", "xls", "pdf" ]
                }
			]
		},
		"fnInitComplete": function ( settings, json ) {
			ss_players_editor.field('team_id').update( json.teamData );
		}
	} );
	
} );

}(jQuery));
