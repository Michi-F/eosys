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
if(isset($_GET['fid']) && is_numeric($_GET['fid']) && isset($_GET['nid']) && is_numeric($_GET['nid'])) {
	$fid = $_GET['fid'];
	$fzrechte = new fzrechte($fid);
	if($fzrechte->fzentschuldigen) {
		$update = new sql();
		//$updatestatement = "UPDATE `fehlzeit` SET `fsid` = '".mysql_real_escape_string($_GET['nid'])."' WHERE `fehlzeit`.`fid` =".mysql_real_escape_string($fid)."";
		$updatestatement = "
			INSERT INTO `fehlzeit` (
			`fid` ,
			`utype` ,
			`userid` ,
			`ffehldatum` ,
			`fgrund` ,
			`fsid` ,
			`feintragedatum` ,
			`faktualisiertdatum` ,
			`faktualisiertvonutype` ,
			`faktualisiertvonuserid` ,
			`fversion`
			)
			
			Select
			  fehlzeit.fid,
			  fehlzeit.utype,
			  fehlzeit.userid,
			  fehlzeit.ffehldatum,
			  fehlzeit.fgrund,
			  '".mysql_real_escape_string($_GET['nid'])."',
			  fehlzeit.feintragedatum,
			  CURRENT_TIMESTAMP,
			  ".$logon->user->usertype.",
			  ".$logon->user->userid.",
			  fehlzeit.fversion + 1
			From
			  fehlzeit
			Where
			  fehlzeit.fid = ".mysql_real_escape_string($fid)."
			   AND fehlzeit.fversion = (
				Select
				  Max(fehlzeitversiontabelle.fversion)
				From
				  fehlzeit AS fehlzeitversiontabelle
				Where
				  fehlzeitversiontabelle.fid = fehlzeit.fid
			  )
		";
		$update->query($updatestatement);
		header("Location: index.php?p=fz&v=".$fid);
	} else {
		echo 'Sie sind nicht dazu berechtigt, diese Entschuldigung zu akzeptieren.';
	}
} else {
	echo 'Es ist ein Fehler aufgetreten.';
}
?>