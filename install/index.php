<?php
/*******************************************************************************************
*    Copyright © 2012-2013 Michael Felger                                                  *
*                                                                                          *
*    This file is part of EOSys.                                                           *
*                                                                                          *
*    EOSys is free software: you can redistribute it and/or modify                         *
*    it under the terms of the GNU General Public License Version 3 as published by        *
*    the Free Software Foundation.                                                         *
*                                                                                          *
*    This program is distributed in the hope that it will be useful,                       *
*    but WITHOUT ANY WARRANTY; without even the implied warranty of                        *
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                         *
*    GNU General Public License Version 3 for more details.                                *
*                                                                                          *
*    You should have received a copy of the GNU General Public License Version 3           *
*    along with EOSys.  If not, see <http://www.gnu.org/licenses/gpl-3.0/>.                *
*                                                                                          *
*    Siehe ./gpl-3.0.txt (GNU GENERAL PUBLIC LICENSE Version 3)                            *
********************************************************************************************/
header("Content-Type: text/html; charset=utf-8");
include('../vlib/vlibTemplate.php');
include("../config.php");
include("../mysql.class.php");
if(isset($_POST['lname']) && !empty($_POST['lvorname']) && !empty($_POST['lkuerzel']) && !empty($_POST['limportedid']) )
{
	$insertlehrer = new sql();
	$insertlehrerstatement = "
		INSERT INTO `lehrer` (`lid`, `limportedid`, `lkuerzel`, `lname`, `lvorname`, `lemail`, `lsendemail`,`lsendemailtutor`) VALUES 
		(NULL, '".mysql_real_escape_string($_POST['limportedid'])."', '".mysql_real_escape_string($_POST['lkuerzel'])."', 
		'".mysql_real_escape_string($_POST['lname'])."', '".mysql_real_escape_string($_POST['lvorname'])."', 
		'".mysql_real_escape_string($_POST['lemail'])."', 1,1);
	";
	$insertlehrer->query($insertlehrerstatement);
	
	$lastid = mysql_insert_id();
	if(!is_numeric($lastid)) {
		$lastid = 2;
	}
	$rechtesql = new sql();
	$rechtestatement = "
		INSERT INTO `userrechte` (
		`lid` ,
		`rid`
		)
		VALUES (
		'".$lastid."', '1'
		), (
		'".$lastid."', '2'
		), (
		'".$lastid."', '3'
		), (
		'".$lastid."', '4'
		), (
		'".$lastid."', '5'
		), (
		'".$lastid."', '6'
		);
	";
	$rechtesql->query($rechtestatement);
	echo 'Erfolgreich erstellt. Sie k&ouml;nnen sich nun einloggen. Bitte L&ouml;schen Sie das Installations-Verzeichnis !';
}
?>
<p>Bitte geben Sie hier den Administrator ein. Dieser muss ein Lehrer sein !</p>
<table border="1">
	<tr>
		<td>Lehrer-Name</td>
		<td>Lehrer-Vorname</td>
		<td>Lehrer-Kürzel</td>
		<td>Login-Kennung</td>
		<td>E-Mail (optional)</td>
		<td></td>
	</tr>
	<form action="index.php" method="POST">
	<tr>
		<td><input type="text" name="lname"></td>
		<td><input type="text" name="lvorname"></td>
		<td><input type="text" name="lkuerzel"></td>
		<td><input type="text" name="limportedid"></td>
		<td><input type="text" name="lemail"></td>
		<td><input type="submit" value="eintragen"></td>
	</tr>
	</form>
</table>