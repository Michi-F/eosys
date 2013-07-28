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
function formatname($name) 
{
	$format = ucfirst(strtolower(trim($name)));
	return $format;
}
function tutor($tutor) {
	$tutorstatement = "
		Select
		  lehrer.lid
		From
		  lehrer
		Where
		  lehrer.lkuerzel = '".$tutor."'
	";
	$tutorquery = mysql_query($tutorstatement);
	$numrows = mysql_num_rows($tutorquery);
	if($numrows == 1) 
	{
		$tutordobj = mysql_fetch_object($tutorquery);
		$tutorid = $tutordobj->lid;
		if(is_numeric($tutorid))
		{
			return $tutorid;
		}
	}
	return false;
}

function geburtsdatum($datum) 
{
	$datearray = explode(".",$datum);
	if($datearray[2] < 1900) {
		if($datearray[2] > 90) {
			$datearray[2] = "19".$datearray[2];
		} elseif($datearray[2] < 90) {
			$datearray[2] = "20".$datearray[2];
		}
	}
	$date = checkdate($datearray[1],$datearray[0],$datearray[2]);
	if($date) 
	{
		//$time = mktime(0,0,0,$datearray[1],$datearray[0],$datearray[0]);
		$time = mktime(0,0,0,$datearray[1],$datearray[0],$datearray[2]);
		return  date('Y-m-d', $time);
	}
	return false;
}

function abijahrgang($jahr) {
	$abijahrgangselect = "
		Select
		  abijahrgang.aid
		From
		  abijahrgang
		Where
		  abijahrgang.aname = '".$jahr."'
	";
	$abijahrgangquery = mysql_query($abijahrgangselect);
	$numrows = mysql_num_rows($abijahrgangquery);
	if($numrows == 1) 
	{
		$abijahrobj = mysql_fetch_object($abijahrgangquery);
		$abijahrid = $abijahrobj->aid;
		if(is_numeric($abijahrid))
		{
			return $abijahrid;
		}
	}
	return false;
}

// hier muss (normalerweise) nichts geändert werden
// Kurstypen und deren Feldbezeichnungen aus Datenbank auslesen
$winprosakursfelder = array();
$winprosakursnummerfelder = array();
$winprosakursfeldersql = new sql();
$winprosakursfelderstatement = "
	Select
	  kurstypen.ktstunden,
	  kurstypen.ktname,
	  kurstypen.ktkuerzel,
	  winprosakurstypen.seminar1,
	  winprosakurstypen.seminar2,
	  winprosakurstypen.seminar3,
	  winprosakurstypen.seminar4,
	  winprosakurstypen.kursnummerseminar1,
	  winprosakurstypen.kursnummerseminar2,
	  winprosakurstypen.kursnummerseminar3,
	  winprosakurstypen.kursnummerseminar4,
	  winprosakurstypen.ktid,
	  winprosakurstypen.reli
	From
	  winprosakurstypen Inner Join
	  kurstypen On winprosakurstypen.ktid = kurstypen.ktid
	WHERE winprosakurstypen.aktiv = 1
";
$winprosakursfeldersql->query($winprosakursfelderstatement);
while($winprosakursfeldersql->fetch_obj()) 
{
	$kurstypenarray[] = $winprosakursfeldersql->result->ktid;
	
	$winprosakursfelder[$winprosakursfeldersql->result->ktid] = array ( // Feldnamen die Daten für 4/2-Stündige unterscheidung beinhalten
		$winprosakursfeldersql->result->seminar1,
		$winprosakursfeldersql->result->seminar2,
		$winprosakursfeldersql->result->seminar3,
		$winprosakursfeldersql->result->seminar4,
	);
	$winprosakursnummerfelder[$winprosakursfeldersql->result->ktid] = array ( // Feldnamen die Kursnummern beinhalten
		$winprosakursfeldersql->result->kursnummerseminar1,
		$winprosakursfeldersql->result->kursnummerseminar2,
		$winprosakursfeldersql->result->kursnummerseminar3,
		$winprosakursfeldersql->result->kursnummerseminar4,
	);
	$kurstypstunden[$winprosakursfeldersql->result->ktid] = $winprosakursfeldersql->result->ktstunden;
	$kurstypname[$winprosakursfeldersql->result->ktid] = array(
		"ktname" => $winprosakursfeldersql->result->ktname,
		"ktkuerzel" => $winprosakursfeldersql->result->ktkuerzel
	);
	
	$relifelder[$winprosakursfeldersql->result->ktid] = $winprosakursfeldersql->result->reli;
}
?>