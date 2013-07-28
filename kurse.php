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
$kurse = new vlibTemplate('kurse.html');
if($logon->user->usertype != 1) {
	header("Location: index.php");
}
if(isset($_GET['loe']) && is_numeric($_GET['loe'])) {
	$loeschsql = new sql();
	$loeschstatement = "
		UPDATE `kurs` SET `lid` = 0 WHERE `kurs`.`kid` =".mysql_real_escape_string($_GET['loe']).";
	";
	$loeschsql->query($loeschstatement);
}
if(isset($_GET['ein']) && is_numeric($_GET['ein'])) {
	$alslehrereintragen = new sql();
	$alslehrereintragenstatement = "
		UPDATE `kurs` SET `lid` = '".$logon->user->userid."' WHERE `kurs`.`kid` =".mysql_real_escape_string($_GET['ein']).";
	";
	$alslehrereintragen->query($alslehrereintragenstatement);
}
if(isset($_POST['lid']) && is_numeric($_POST['kid']) && isset($_POST['kid']) && is_numeric($_POST['kid'])) {
	$alslehrereintragen = new sql();
	$alslehrereintragenstatement = "
		UPDATE `kurs` SET `lid` = '".mysql_real_escape_string($_POST['lid'])."' WHERE `kurs`.`kid` =".mysql_real_escape_string($_POST['kid']).";
	";
	$alslehrereintragen->query($alslehrereintragenstatement);
}

$kursabfrage = new sql();
$kursabfragestatement = "
	Select
	  kurs.kid,
	  kurstypen.ktname,
	  kurstypen.ktkuerzel,
	  kurs.knummer,
	  abijahrgang.aname,
	  lehrer.lid,
	  lehrer.lname,
	  IF(COUNT(*) = 1, IF(besucht.sid > 0, 1, 0) , COUNT(*)) As schueleranzahl,
	  IF(kurs.lid = '".$logon->user->userid."', '0','1') as kurseintragen
	From
	  abijahrgang Inner Join
	  kurs On abijahrgang.aid = kurs.aid Inner Join
	  kurstypen On kurstypen.ktid = kurs.ktid LEFT OUTER JOIN
	  lehrer On (lehrer.lid = kurs.lid) LEFT OUTER JOIN 
	  besucht On besucht.kid = kurs.kid
	Group By
	  kurs.kid
	ORDER BY
	  abijahrgang.aid, kurstypen.ktname, kurs.knummer
";
$kursabfrage->query($kursabfragestatement);
while($kursabfrage->fetch_obj()) {
	$kursarray[] = array(
		"kid"=>$kursabfrage->result->kid,
		"ktname"=>$kursabfrage->result->ktname,
		"ktkuerzel"=>$kursabfrage->result->ktkuerzel,
		"knummer"=>$kursabfrage->result->knummer,
		"aname"=>$kursabfrage->result->aname,
		"lid"=>$kursabfrage->result->lid,
		"lname"=>$kursabfrage->result->lname,
		"schueleranzahl"=>$kursabfrage->result->schueleranzahl,
		"kurseintragen"=>$kursabfrage->result->kurseintragen,
		"lehrerarray"=>array()
	);
}

if(isset($_POST["ktid"]) && is_numeric($_POST["ktid"]) && isset($_POST["aid"]) && is_numeric($_POST["aid"]))
{
	if($_POST["ktid"] > 0 && $_POST["aid"] > 0)
	{
		$neuerkurssql = new sql();
		$neuerkursstatement = "
			INSERT IGNORE INTO `kurs` (`kid`, `ktid`, `knummer`, `aid`, `kversion`, `lid`) VALUES 
			(NULL, '".mysql_real_escape_string($_POST["ktid"])."', '".mysql_real_escape_string($_POST["knummer"])."', '".mysql_real_escape_string($_POST["aid"])."', '0', NULL);
		";
		$neuerkurssql->query($neuerkursstatement);
		header("Location: index.php?p=ku");
	}
}

// Felder für neuen Kurs füllen
$selectktidsql = new sql();
$selectktidstatement = "
	Select
	  kurstypen.ktid,
	  kurstypen.ktname,
	  kurstypen.ktkuerzel,
	  kurstypen.ktstunden
	From
	  kurstypen
	Order By
	  kurstypen.ktname ASC,
	  kurstypen.ktstunden
";
$selectktidsql->query($selectktidstatement);
$kurse->setdbloop('selectktid', $selectktidsql->query);

$selectaiddsql = new sql();
$selectaiddstatement = "
	Select
	  abijahrgang.aid,
	  abijahrgang.aname
	From
	  abijahrgang
	Where
	  abijahrgang.aname >= ".date('Y')."
	Order By
	  abijahrgang.aid
";
$selectaiddsql->query($selectaiddstatement);
$kurse->setdbloop('selectaid', $selectaiddsql->query);

/*
Auskommentiert, da das erzeugte Array zu groß wird und von der Template-Engine nicht mehr verarbeitet wird.

if($rechte->kursbearbeiten) {
	$kurse->setvar('kursbearbeiten', '1');
	$lehrersql = new sql();
	$lehrerstatement = "
	Select
	  lehrer.lid,
	  lehrer.lname,
	  lehrer.lvorname,
	  lehrer.lkuerzel
	From
	  lehrer
	";
	$lehrersql->query($lehrerstatement);
	while($lehrersql->fetch_obj()) {
		$lehrerarray[] = array(
			"klid"=>$lehrersql->result->lid,
			"klname"=>$lehrersql->result->lname.', '.$lehrersql->result->lvorname.' ('.$lehrersql->result->lkuerzel.')',
		);
	}
	for($i=0;$i<count($kursarray);$i++) {
		$kursarray[$i]["lehrerarray"] = $lehrerarray;
		for($j=0;$j<count($kursarray[$i]["lehrerarray"]);$j++) {
			if($kursarray[$i]["lehrerarray"][$j]["klid"] == $kursarray[$i]["lid"]) {
				$kursarray[$i]["lehrerarray"][$j]['lselected'] = '1';
			}
		}
	}
}*/
$kurse->setloop('kurse', $kursarray);
$kurse->pparse();
?>