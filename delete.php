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
$deletetmpl = new vlibTemplate('delete.html');
function delfehler($msg = '') {
	global $deletetmpl;
	if($msg == ''){
		$deletetmpl->setvar("fehlermsg","Es ist ein Fehler aufgetreten.");
	} else {
		$deletetmpl->setvar("fehlermsg",$msg);
	}
	$deletetmpl->setvar("fehler","1");
	$deletetmpl->pparse();
	exit();
}

if(isset($_POST['f'])) {
	$fid = $_POST['f'];
} elseif(isset($_GET['f'])) {
	$fid = $_GET['f'];
} else {
	delfehler();
}
if(!is_numeric($fid)) {
	delfehler();
}
$delfzrechte = new fzrechte($fid);
if(!$delfzrechte->fzloeschen){
	delfehler('Sie sind nicht berechtigt, diese Fehlzeit zu l&ouml;schen.');
}
if(isset($_POST['f'])) {
	$delsql = new sql();
	/*$delstatement = "
		UPDATE `fehlzeit` SET `fsid` = '7' WHERE `fehlzeit`.`fid` =".mysql_real_escape_string($fid).";
	";*/
	$delstatement = "
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
			  7,
			  fehlzeit.feintragedatum,
			  CURRENT_TIMESTAMP,
			  ".$logon->user->usertype.",
			  ".$logon->user->userid.",
			  fehlzeit.fversion + 1
			From
			  fehlzeit
			Where
			  `fehlzeit`.`fid` =".mysql_real_escape_string($fid)."
			  AND fehlzeit.fversion = (
				Select
				  Max(fehlzeitversiontabelle.fversion)
				From
				  fehlzeit AS fehlzeitversiontabelle
				Where
				  fehlzeitversiontabelle.fid = fehlzeit.fid
			  )
	";
	$delsql->query($delstatement);
	$deletetmpl->setvar("erfolgreich","1");
	$deletetmpl->setvar("fid",$_GET['f']);
} elseif(isset($_GET['f'])) {
	$deletetmpl->setvar("fragen","1");
	$deletetmpl->setvar("fid",$_GET['f']);
} else {
	delfehler();
}
$deletetmpl->pparse();
?>