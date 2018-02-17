<?php
	/*********************************************************************
	Coppermine to NextGen Gallery (NGG) migration tool

	Copyright 2010 by Otto J. Simon, Graz, Austria
	E-Mail: os@simonconsulting.at
	Web: http://www.simonconsulting.at

	Version 0.1 - Oct. 8, 2010

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	***********************************************************************/

	// mysql host name (usually: localhost)
	define("DBHOST", "localhost");

	// mysql database name
	define("DBNAME", "abingdon_wp");

	// mysql username
	define("DBUSER", "abingdon_wp");

	// myswql password
	define('DBPASS', 'xSh703p8pP');

	// Name of Coppermine's album table

	define ("COPPERMINE_ALBUMS", "cpg_albums");
	// Name of Coppermine's picture table
	define ("COPPERMINE_PICTURES", "cpg_pictures");

	// absolute pathe where Coppermine had stored pictures
	define ("COPPERMINE_PATH", "/home/abingdon/public_html/photogallery/albums/");

	// Name of NGG's gallery table
	define ("NGG_GALLERY", "wp_ngg_gallery");

	// Name of NGG's picture table
	define ("NGG_PICTURES", "wp_ngg_pictures");

	// absolute pathe where NGG will store migrated pictures
	define ("NGG_PATH", "/home/abingdon/public_html/wp-content/gallery/userpics");

	// relative path to WP's home dir where NGG will store migrated pictures
	define ("NGG_REL_PATH", "wp-content/gallery/userpics");

	// character set used for mysql DB
	define ("CHARSET", "utf8");

	function quote_smart($value)
	{
		// Stripslashes
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		// Quote if not a number or a numeric string
		if (!is_numeric($value) && ($value != "NULL")) {
			$value = "'" . mysql_real_escape_string($value) . "'";
		}
		return $value;
	}

?><!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	</head>
	<body>
		<?
			// Database Connection
			if (!defined ("DBHOST")) define ("DBHOST", "127.0.0.1");

			$db = mysql_connect (DBHOST, DBUSER, DBPASS);
			mysql_select_db (DBNAME, $db);

			if (!$db) {
				header ("HTTP/1.0 503 Service Unavailable");
				echo "<P>Cannot connect to DB</P>";
				exit ();
			};

			mysql_query("SET CHARACTER SET " . CHARSET);
			mysql_query("SET NAMES " . CHARSET);

			$aid = -1;
			$gid = -1;
			$path = "";
			$rel_path = "";
			$query = "select * from " . COPPERMINE_PICTURES . " order by aid, pid";
			$pictures = mysql_query ($query);
			while ($picture = mysql_fetch_object ($pictures)) {
				if ($picture->aid != $aid) {
					// new album
					$query = "select * from " . COPPERMINE_ALBUMS . " where aid=" . $picture->aid;
					$albums = mysql_query ($query);
					if ($album = mysql_fetch_object ($albums)) {
						$aid = $album->aid;
						$path = NGG_PATH . "/" . $album->aid;
						$rel_path = NGG_REL_PATH . "/" . $album->aid;
						$name = "coppermine-" . $album->aid;
                                                mkdir ($path);
						if  (mkdir ($path . "/thumbs")) {
							$query = sprintf ("insert into " . NGG_GALLERY . " (name, path, title, galdesc) values (%s, %s, %s, %s)",
							quote_smart($name),
							quote_smart($rel_path),
							quote_smart($album->title),
							quote_smart($album->description)
							);
							mysql_query ($query);
							$gid = mysql_insert_id();
						} else {
							$err[] = "Album already imported - skipping: " . $album->aid;
							$aid = -1;
							$gid = -1;
						}
					} else {
						$err[] = "Coppermine album missing: " . $picture->aid;
						$gid = -1;
					}
				}
				if ($gid != -1) {
					$query = sprintf ("insert into " . NGG_PICTURES . " (galleryid, filename, description, imagedate) values (%s, %s, %s, %s)",
					quote_smart($gid),
					quote_smart($picture->filename),
					quote_smart($picture->caption),
					quote_smart($picture->mtime)
					);
					mysql_query ($query);
					$copy = copy (COPPERMINE_PATH . $picture->filepath . $picture->filename, $path . "/" . $picture->filename);
					if (!$copy) {
						$err[] = "could not copy to: " . $path . "/" . $picture->filename;
					}
				}
			}
			if (empty ($err)) {
				echo "<P>Import Succesfull.</P>";
			} else {
				echo "<P>";
				while (list($key, $value) = each($err)) {
					echo $value, "<BR/>\n";
				}
				echo "</P>";
			}
		?>
	</body>
</html>