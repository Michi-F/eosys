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
$deletejgtmpl = new vlibTemplate("deletejg.html");

function deletejgfehler($error)
{
	die($error);
}
if($logon->user->usertype != 1 || $rechte->adminfunktionen != true)
{
	header("Location: index.php");
}

if(isset($_POST['aid']) && isset($_POST['best']) && is_numeric($_POST['aid']) && $_POST['best'] == 1)
{
	$aidsql = new sql();
	$aidstatement = "
		Select
		  abijahrgang.aname,
		  abijahrgang.aid
		From
		  abijahrgang
		Where
		  abijahrgang.aid = '".mysql_real_escape_string($_POST['aid'])."'
	";
	$aidsql->query($aidstatement);
	$aidsql->fetch_obj();
	$aid = $aidsql->result->aid;
	if($aidsql->num_rows() != 1 || empty($aid))
	{
		deletejgfehler("Der Jahrgang konnte nicht gefunden werden.");
	}

	// Alle zu löschende Fehlzeiten zwischenspeichern
	$zuloeschendefz = array();
	$zuloeschendefzsql = new sql();
	$zuloeschendefzstatement = "
		Select
		  DISTINCT fehlzeit.fid
		From
		  abijahrgang Inner Join
		  schueler On abijahrgang.aid = schueler.aid Left Join
		  fehlzeitenschueler On schueler.sid = fehlzeitenschueler.sid Inner Join
		  fehlzeit On fehlzeitenschueler.fid = fehlzeit.fid
		Where
		  abijahrgang.aid = '".$aid."'
	";
	$zuloeschendefzsql->query($zuloeschendefzstatement);
	while($zuloeschendefzsql->fetch_obj())
	{
		$zuloeschendefz[] = $zuloeschendefzsql->result->fid;
	}

	// Alle Kurs-Verknüpfungen der betroffenen Fehlzeiten löschen (Tabelle fehlzeitenkurse)
	$fzkursesql = new sql();
	$fzkursestatement = "
		Delete From
		fehlzeitenkurse Where fehlzeitenkurse.fid 
		IN (".implode($zuloeschendefz,',').")
	";
	$fzkursesql->query($fzkursestatement);

	// Alle Schueler-Verknüpfungen der betroffenen Fehlzeiten löschen (Tabelle fehlzeitenschueler)
	$fzschuelersql = new sql();
	$fzschuelerstatement = "
		Delete From
		fehlzeitenschueler Where fehlzeitenschueler.fid
		IN (".implode($zuloeschendefz,',').")
	";
	$fzschuelersql->query($fzschuelerstatement);

	// Alle Fehlzeiten löschen
	$fzsql = new sql();
	$fzstatement = "
		Delete From
		fehlzeit Where fehlzeit.fid IN (".implode($zuloeschendefz,',').");
	";
	$fzsql->query($fzstatement);

	// Alle besucht-Einträge der Schüler löschen
	$besuchtsql = new sql();
	$besuchtsatement = "
		Delete From
		besucht Where besucht.sid IN
		(
		  Select
			DISTINCT schueler.sid
		  From
			abijahrgang Inner Join
			schueler On abijahrgang.aid = schueler.aid
			Where
			  abijahrgang.aid = '".$aid."'
		)
	";
	$besuchtsql->query($besuchtsatement);

	// Alle kurse löschen
	$kursesql = new sql();
	$kursestatement = "
		Delete From
		kurs Where kurs.aid = '".$aid."'
	";
	$kursesql->query($kursestatement);

	// Alle schueler löschen
	$schuelersql = new sql();
	$schuelerstatement = "
		Delete From
		schueler Where schueler.aid = '".$aid."'
	";
	$schuelersql->query($schuelerstatement);

	// Abijahrgang lösche
	$abijahrgangsql = new sql();
	$abijahrgangstatement = "
		Delete From
		abijahrgang Where abijahrgang.aid = '".$aid."'
	";
	$abijahrgangsql->query($abijahrgangstatement);
	
	$deletejgtmpl->setvar("success",1);
}
elseif(isset($_POST['aid']) && !isset($_POST['best']))
{
	$deletejgtmpl->setvar("delaid",$_POST['aid']);
	
	$anamesql = new sql();
	$anamestatement = "
		Select
		  abijahrgang.aname,
		  abijahrgang.aid
		From
		  abijahrgang
		Where
		  abijahrgang.aid = '".mysql_real_escape_string($_POST['aid'])."'
	";
	$anamesql->query($anamestatement);
	$anamesql->fetch_obj();
	$aid = $anamesql->result->aid;
	$aname = $anamesql->result->aname;
	if($anamesql->num_rows() != 1 || empty($aid))
	{
		deletejgfehler("Der Jahrgang konnte nicht gefunden werden.");
	}
	$deletejgtmpl->setvar("delaname",$aname);
}
else
{
	$anamesql = new sql();
	$anamestatement = "
		Select
		  abijahrgang.aname,
		  abijahrgang.aid
		From
		  abijahrgang
		Where
		  abijahrgang.aid != 1
	";
	$anamesql->query($anamestatement);
	
	$deletejgtmpl->setdbloop("jahrgangloop",$anamesql->query);
}
$deletejgtmpl->pparse();
?>