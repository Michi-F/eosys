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
function formstunden($where,$kursarray) {
	$stundensql = new sql();
	$stundenstatement = "
		Select
		  stunden.stunde,
		  stunden.stname
		From
		  stunden
		WHERE
		  stunden.stunde ".$where."
	";
	$stundensql->query($stundenstatement);
	while($stundensql->fetch_obj()) {
		$stundenarray[] = array(
			"stunde"=>$stundensql->result->stunde,
			"stname"=>$stundensql->result->stname,
			"kurse"=>$kursarray
		);
	}
	return $stundenarray;
}
function viewstunden($where) { // Alle Stunden auslesen, in denen man gefehlt hat
	$kursesql = new sql();
	$kursesqlstatement = "
		Select DISTINCT
		  kurstypen.ktname,
		  kurstypen.ktkuerzel,
		  kurs.knummer,
		  kurs.kid,
		  abijahrgang.aname,
		  fehlzeitenkurse.stunde,
		  1 as selected
		From
		  fehlzeit Inner Join
		  fehlzeitenkurse On fehlzeit.fid = fehlzeitenkurse.fid Inner Join
		  kurs On fehlzeitenkurse.kid = kurs.kid Inner Join
		  kurstypen On kurs.ktid = kurstypen.ktid Inner Join
		  abijahrgang On kurs.aid = abijahrgang.aid
		Where
		  fehlzeit.fid = ".mysql_real_escape_string($_GET['v'])."		  
		  AND fehlzeitenkurse.stunde ".$where."
		ORDER BY
		  fehlzeitenkurse.stunde
	";
	$kursesql->query($kursesqlstatement);
	while($kursesql->fetch_obj()) {
		$stundenarray[] = array(
			"stunde"=>$kursesql->result->stunde,
			"stname"=>$kursesql->result->stname,
			"kurse"=>array(
				array(
					"kid"=>$kursesql->result->kid,
					"ktname"=>$kursesql->result->ktname,
					"ktkuerzel"=>$kursesql->result->ktkuerzel,
					"knummer"=>$kursesql->result->knummer,
					"aname"=>$kursesql->result->aname,
					"selected"=>$kursesql->result->selected
				)
			)
		);
	}
	return $stundenarray;
}
function newfzfehler($msg='') {
	$newfzfehlertmpl = new vlibTemplate('newfzfehler.html');
	if($msg == '') {
		$messeage = "Bitte wenden Sie sich an den Administrator.";
	} else {
		$messeage = $msg;
	}	
	$newfzfehlertmpl->setvar("fehlermsg",$messeage);
	$newfzfehlertmpl->pparse();
	exit();
};
function schuelerform($statusselected) {
	global $logon;	
	$usersql = new sql();
	$userstatement = '
		Select Distinct
		  schueler.sname,
		  schueler.svorname,
		  schueler.sid
		From
		  schueler
		Where
		  schueler.sid = '.$logon->user->userid.'
	';
	$usersql->query($userstatement);
	
	// Kurse zum auswählen:
	$kursbelegung = "
		Select
		  kurs.kid,
		  kurstypen.ktname,
		  kurstypen.ktkuerzel,
		  kurs.knummer
		From
		  kurs Inner Join
		  besucht On kurs.kid = besucht.kid Inner Join
		  schueler On schueler.sid = besucht.sid Inner Join
		  kurstypen On kurstypen.ktid = kurs.ktid
		Where
		  schueler.sid = '".$logon->user->userid."'
		ORDER BY
		  kurstypen.ktname ASC
	";
	$kursbelegungsql = new sql();
	$kursbelegungsql->query($kursbelegung);
	$kursarray = false;
	while($kursbelegungsql->fetch_obj()) {
		$kursarray[] = array(
			"kid"=>$kursbelegungsql->result->kid,
			"ktname"=>$kursbelegungsql->result->ktname,
			"ktkuerzel"=>$kursbelegungsql->result->ktkuerzel,
			"knummer"=>$kursbelegungsql->result->knummer,
			"checked"=>$kursbelegungsql->result->checked
		);
	}
	
	$stundenvmarray = formstunden("<=6",$kursarray);
	$stundennmarray = formstunden(">6",$kursarray);
	
	// Status-Feld
	$statusstatement = "
	Select
	  fehlzeitstatus.fsid,
	  fehlzeitstatus.fstyp,
	  fehlzeitstatus.fsname,
	  IF(fehlzeitstatus.fsid IN (0".$statusselected."),'1','0') as selected
	From
	  fehlzeitstatus
	Where
	  fehlzeitstatus.fstyp = 1
	";
	$statussql = new sql();
	$statussql->query($statusstatement);
	return array(
		'schuelerselect'=>$usersql->query,
		'stundenvm'=>$stundenvmarray,
		'stundennm'=>$stundennmarray,
		'status'=>$statussql->query,
	);
}
function lehrerform($userselected, $statusselected, $loadschuelerfromkurs)
{
	global $logon;
	
	if(is_array($userselected) && count($userselected) > 0)
	{
		// Wenn bereits Schüler ausgewählt wurden
		$userselected = ",".implode($userselected,",");
		$userselectedsql = new sql();
		$userselectedstatement = 
		"
			Select Distinct
			  schueler.sname,
			  schueler.svorname,
			  schueler.sid
			From
			  lehrer Inner Join
			  kurs On lehrer.lid = kurs.lid Inner Join
			  besucht On kurs.kid = besucht.kid Inner Join
			  schueler On besucht.sid = schueler.sid Inner Join
			  abijahrgang On schueler.aid = abijahrgang.aid
			Where
			  lehrer.lid = ".$logon->user->userid." And
			  abijahrgang.aname >= ".date('Y')." And
			  schueler.sid IN (0".$userselected.")
		";
		$userselectedsql->query($userselectedstatement);
	}
	elseif(!empty($loadschuelerfromkurs) && is_numeric($loadschuelerfromkurs) && $loadschuelerfromkurs != 1)
	{
		$userselectedsql = new sql();
		$userselectedstatement = 
		"
			Select Distinct
			  schueler.sname,
			  schueler.svorname,
			  schueler.sid
			From
			  lehrer Inner Join
			  kurs On lehrer.lid = kurs.lid Inner Join
			  besucht On kurs.kid = besucht.kid Inner Join
			  schueler On besucht.sid = schueler.sid Inner Join
			  abijahrgang On schueler.aid = abijahrgang.aid
			Where
			  lehrer.lid = ".$logon->user->userid." And
			  abijahrgang.aname >= ".date('Y')." And
			  kurs.kid = '".mysql_real_escape_string($loadschuelerfromkurs)."'
		";
		$userselectedsql->query($userselectedstatement);
	}

	$usersql = new sql();
	$userstatement = "
		Select Distinct
		  schueler.sname,
		  schueler.svorname,
		  schueler.sid
		From
		  lehrer Inner Join
		  kurs On lehrer.lid = kurs.lid Inner Join
		  besucht On kurs.kid = besucht.kid Inner Join
		  schueler On besucht.sid = schueler.sid Inner Join
		  abijahrgang On schueler.aid = abijahrgang.aid
		Where
		  lehrer.lid = ".$logon->user->userid." And
		  abijahrgang.aname >= ".date('Y')."
	";
	$usersql->query($userstatement);
	
	// Kurse zum auswählen:
	$kursbelegungsql = new sql();
	$kursbelegung = "
		Select
		  kurstypen.ktname,
		  kurstypen.ktkuerzel,
		  kurs.knummer,
		  kurs.kid,
		  abijahrgang.aname
		From
		  kurs Inner Join
		  lehrer On kurs.lid = lehrer.lid Inner Join
		  abijahrgang On abijahrgang.aid = kurs.aid Inner Join
		  kurstypen On kurs.ktid = kurstypen.ktid
		Where
		  kurs.kid = 1 OR
		  (
		  lehrer.lid = ".$logon->user->userid." And
		  abijahrgang.aname >= ".date("Y")."
		  )
		Order By
		  kurstypen.ktname, abijahrgang.aname
	";
	$kursbelegungsql->query($kursbelegung);
	$kursarray = false;
	while($kursbelegungsql->fetch_obj()) {
		$kursarray[] = array(
			"kid"=>$kursbelegungsql->result->kid,
			"ktname"=>$kursbelegungsql->result->ktname,
			"ktkuerzel"=>$kursbelegungsql->result->ktkuerzel,
			"knummer"=>$kursbelegungsql->result->knummer,
			"aname"=>$kursbelegungsql->result->aname
		);
	}
	
	$stundenvmarray = formstunden("<=6",$kursarray);
	$stundennmarray = formstunden(">6",$kursarray);
	
	// Status-Feld
	$statusstatement = "
		Select
		  fehlzeitstatus.fsid,
		  fehlzeitstatus.fstyp,
		  fehlzeitstatus.fsname,
		  IF(fehlzeitstatus.fsid IN (0".$statusselected."),'1','0') as selected
		From
		  fehlzeitstatus
		Where
		  `fstyp` = 4 OR
		  fsid IN (9)

	";
	$statussql = new sql();
	$statussql->query($statusstatement);
	
	$kurseladenarray = $kursarray;
	foreach($kurseladenarray as $kurseladenarrayid => $kurseladenarrayvalue)
	{
		if($kurseladenarrayvalue["kid"] == 1)
		{
			unset($kurseladenarray[$kurseladenarrayid]);
		}
	}
	
	return array(
		'schuelerselect'=>$usersql->query,
		'schuelerselected'=>$userselectedsql->query,
		'stundenvm'=>$stundenvmarray,
		'stundennm'=>$stundennmarray,
		'status'=>$statussql->query,
		'kursarray'=>$kurseladenarray
	);
}

$newfztmpl = new vlibTemplate('newfz.html');
if($logon->user->usertype == 1) {
	$newfztmpl->setvar("usertype","1");
	$newfztmpl->setvar("abijahrgang","1");
} elseif($logon->user->usertype == 0) {
	$newfztmpl->setvar("usertype","0");
	$newfztmpl->setvar("abijahrgang","0");
} else {
	$logon->logout();
}

if(isset($_GET['v']) && is_numeric($_GET['v'])) {
	
	$fzrechte = new fzrechte($_GET['v']);
	if(!$fzrechte->fzlesen) {
		newfzfehler('Sie sind nicht berechtigt, diese Seite zu öffnen.');
	}
	
	$stundenvmarray = viewstunden("<=6");
	$stundennmarray = viewstunden(">6");
	$newfztmpl->setloop('stundenvm', $stundenvmarray);
	$newfztmpl->setloop('stundennm', $stundennmarray);
	
	$fehlzeit = new sql();
	$fehlzeitstatement = "
		Select
		  fehlzeit.fid,
		  Date_Format(fehlzeit.ffehldatum, '%d.%m.%Y') As ffehldatum,
		  fehlzeit.fgrund,
		  Date_Format(fehlzeit.feintragedatum, '%d.%m.%Y um %H:%i Uhr') As
		  feintragedatum,
		  fehlzeitstatus.fsid,
		  fehlzeitstatus.fstyp,
		  fehlzeitstatus.fsname,
		  Case fehlzeit.faktualisiertvonutype When 0 Then schueler1.sname
			When 1 Then lehrer1.lkuerzel Else Null End As faktualisiertvonusername,
		  Case fehlzeit.faktualisiertvonutype When 0 Then schueler1.svorname
			When 1 Then lehrer1.lname Else Null End As faktualisiertvonuservorname,
		  fehlzeit.faktualisiertvonutype,
		  Date_Format(fehlzeit.faktualisiertdatum, '%d.%m.%Y um %H:%i Uhr') As faktualisiertdatum
		From
		  fehlzeit Inner Join
		  fehlzeitstatus On fehlzeitstatus.fsid = fehlzeit.fsid Left Join
		  lehrer lehrer1 On lehrer1.lid = fehlzeit.faktualisiertvonuserid Left Join
		  schueler schueler1 On schueler1.sid = fehlzeit.faktualisiertvonuserid
		Where
		  fehlzeit.fid = '".mysql_real_escape_string($_GET['v'])."' And
		  fehlzeit.fversion = (Select
			Max(fehlzeitversiontabelle.fversion)
		  From
			fehlzeit As fehlzeitversiontabelle
		  Where
			fehlzeitversiontabelle.fid = fehlzeit.fid)
	";
	$fehlzeit->query($fehlzeitstatement);
	$fehlzeit->fetch_obj();
	
	$schuelerselectarray = array();
	$schuelerselectsql = new sql();
	$schuelerselectstatement = "
		Select
		  schueler.sid,
		  schueler.sname,
		  schueler.svorname,
		  lehrer.lkuerzel as tutorkuerzel,
		  lehrer.lname as tutorname,
		  If(Date_Sub(CurDate(), Interval 18 Year) >= schueler.sgeburtsdatum, '1','0') As volljaehrig
		FROM
		  fehlzeitenschueler Inner Join
		  schueler On fehlzeitenschueler.sid = schueler.sid
		  Inner Join
		  lehrer On schueler.lid = lehrer.lid
		Where
		  fehlzeitenschueler.fid = '".mysql_real_escape_string($_GET['v'])."';
	";
	$schuelerselectsql->query($schuelerselectstatement);
	while($schuelerselectsql->fetch_obj()) {
		$schuelerselectarray[] = array(
			"sid"=>$schuelerselectsql->result->sid,
			"sname"=>$schuelerselectsql->result->sname,
			"svorname"=>$schuelerselectsql->result->svorname,
			"tutorkuerzel"=>$schuelerselectsql->result->tutorkuerzel,
			"tutorname"=>$schuelerselectsql->result->tutorname,
			"volljaehrig"=>$schuelerselectsql->result->volljaehrig
		);
	}
	if(count($schuelerselectarray) > 1)
	{
		$newfztmpl->setvar("volljaehrig",1);
	}
	elseif(isset($schuelerselectarray[0]["volljaehrig"]) && $schuelerselectarray[0]["volljaehrig"] === 1)
	{
		$newfztmpl->setvar("volljaehrig",1);
	}
	else
	{
		$newfztmpl->setvar("volljaehrig",0);
	}
	
	$newfztmpl->setloop('schuelerselect',$schuelerselectarray);
	$newfztmpl->setvar("datum",$fehlzeit->result->ffehldatum);
	$newfztmpl->setvar("fgrund",$fehlzeit->result->fgrund);
	$newfztmpl->setvar("feintragedatum",$fehlzeit->result->feintragedatum);
	$newfztmpl->setvar("faktualisiertdatum",$fehlzeit->result->faktualisiertdatum);
	$newfztmpl->setvar("faktualisiertvonuservorname",$fehlzeit->result->faktualisiertvonuservorname);
	$newfztmpl->setvar("faktualisiertvonusername",$fehlzeit->result->faktualisiertvonusername);
	if($fehlzeit->result->faktualisiertvonutype == 1) {
		$newfztmpl->setvar("faktualisiertvonusertype",1);
	}
	$newfztmpl->setvar("fsname",$fehlzeit->result->fsname);
	$newfztmpl->setvar("fsid",$fehlzeit->result->fsid);
	$newfztmpl->setvar("printfid",$fehlzeit->result->fid);
	
	if($fzrechte->fzentschuldigen && ($fehlzeit->result->fstyp == 1)) 
	{
		$newfztmpl->setvar("fzentschuldigen","1");
		$newfztmpl->setvar("fid",$fehlzeit->result->fid);
		if($fehlzeit->result->fsid == 1) { // warte auf bestätigung
			$newfztmpl->setvar("nid",3); // Entschuldigung akzeptiert
			$newfztmpl->setvar("nida",8); // Entschuldigt mit Attest
		}elseif($fehlzeit->result->fsid == 5){ // beurlaubung beantragt
			$newfztmpl->setvar("nid",6); // beurlaubung bestätigen
		}else{
			$newfztmpl->setvar("nid",'null');
		}
		$newfztmpl->setvar("nidn",'2'); // Entschuldigung nicht akzeptieren
	}
	$newfztmpl->setvar("view","1");
	
} elseif(isset($_POST['fgrund'])) {
	$datenfehler = false;
	$datenfehlerdaten = array(
		"datum" => 'TT.MM.JJJJ',
		"sid" => '',
		"fgrund" => '',
		"status" => '',
	);
	
	$newfztmpl->setvar("post","1");
	if($_POST['datum']=='TT.MM.JJJJ' || !datum($_POST['datum'])) {
		$datenfehler = true;
	} else {
		$datenfehlerdaten['datum'] = $_POST['datum'];
		$ins['datum'] = datum($_POST['datum']);
	}
	if(!empty($_POST['fgrund'])) {
		$datenfehlerdaten['fgrund'] = $_POST['fgrund'];
		$ins['fgrund'] = $_POST['fgrund'];
	} else {
		$datenfehler = true;
	}
	if($logon->user->usertype == 1) {// lehrer
		
		// Array der ausgewählten Schüler, die von Lehrer entschuldigt werden sollen
		$insschuelerarray = array();		
		foreach($_POST['sid'] as $postkey => $postval) 
		{
			if(is_numeric($postval) && !empty($postval)) 
			{
				$insschuelerarray[] = $postval;
			}
		}
		if(count($insschuelerarray) == 0)
		{
			$datenfehler = true;
		}
		else
		{
			$ins['sid'] = $insschuelerarray;
			$datenfehlerdaten['sid'] = $insschuelerarray;
		}
		// Status überprüfung
		if(empty($_POST['status']) || !is_numeric($_POST['status'])) 
		{
			$datenfehler = true;
		} 
		elseif(!in_array($_POST['status'],array("4","9"))) 
		{
			newfzfehler("Status wurde falsch übergeben. <br> Bitte wenden Sie sich an den Administrator.");
		}
		elseif($_POST['status'] == '4' && count($insschuelerarray) > 1) // "Entschuldigung liegt nicht vor" und mehr als 1 Schüler ausgewählt
		{
			newfzfehler('"Entschuldigung liegt nicht vor" kann nur für einen einzelnen Schüler eingetragen werden!<br><br><a href="index.php?p=fz">Zurück</a>');
		}
		else 
		{			
			$datenfehlerdaten['status'] = ','.$_POST['status'];
			$ins['status'] = $_POST['status'];
		}
	} elseif($logon->user->usertype == 0) { // schüler
		$ins['sid'] = array(0=>$logon->user->userid);
		if(empty($_POST['status']) || !is_numeric($_POST['status'])) {
			$datenfehler = true;
		} elseif(!in_array($_POST['status'],array("1","5"))) {
			newfzfehler("Status wurde falsch übergeben. <br> Bitte wenden Sie sich an den Administrator.");
		} else {
			$datenfehlerdaten['status'] = ','.$_POST['status'];
			$ins['status'] = $_POST['status'];
		}
	} else {
		$logon->logout();
	}
	$inskursearray = array();
	if(is_array($_POST['kurs'])) {
		foreach($_POST['kurs'] as $postkey => $postval) {
			if(is_numeric($postkey) && is_numeric($postval)) {
				if(!empty($postval)) {
					$inskursearray[$postkey] = $postval;
				}
			} else {
				$datenfehler = true;
			}
		}
	} else {
		$datenfehler = true;
	}
	if(count($inskursearray) < 1) {
		$datenfehler = true;
	}
	if($_POST['status'] == '9') 
	{		
		foreach($inskursearray as $kursid)
		{
			if($kursid != 1)
			{
				// "Entschuldigt durch Fachlehrer gewählt, aber dann nicht allgemeinen Kurs sondern einen bestimmten Kurs ausgewählt -> Fehler. Bei Entschuldigungen für ganzen Kurs muss immer der allgemeine Kurs (kursid (kid) = 1) gewählt werden !
				$datenfehler = true;
			}
		}
	}
	if($datenfehler) {
		foreach($inskursearray as $stunde => $kursid) {
			$selectedkursearray[] = array(
				"stid"=>$stunde,
				"kid"=>$kursid
			);
		}
		$newfztmpl->setloop("selectedkurse",$selectedkursearray);
		if($logon->user->usertype == 1) {
			$form = lehrerform($datenfehlerdaten['sid'],$datenfehlerdaten['status']);
		} elseif($logon->user->usertype == 0) {
			$form = schuelerform($datenfehlerdaten['status']);
		} else {
			$logon->logout();
		}
		
		if($logon->user->usertype == 1)
		{
			$newfztmpl->setloop("kurseladenloop",$form["kursarray"]);
		}
		$newfztmpl->setdbloop("schuelerselect",$form["schuelerselect"]);
		$newfztmpl->setdbloop("schuelerselected",$form["schuelerselected"]);
		$newfztmpl->setloop('stundenvm', $form["stundenvm"]);
		$newfztmpl->setloop('stundennm', $form["stundennm"]);
		$newfztmpl->setdbloop('status', $form["status"]);
		$newfztmpl->setvar("fgrund",$datenfehlerdaten['fgrund']);
		$newfztmpl->setvar("datum",$datenfehlerdaten['datum']);
	} else {
		$insert = new sql();
		$insertstatement = "
			INSERT INTO `fehlzeit` (`fid`, `utype`, `userid`, `ffehldatum`, `fgrund`, `fsid`, `faktualisiertdatum`,`faktualisiertvonutype`,`faktualisiertvonuserid`,`fversion`) VALUES (
			NULL,
			'".$logon->user->usertype."',
			'".$logon->user->userid."',
			'".mysql_real_escape_string($ins['datum'])."',
			'".mysql_real_escape_string($ins['fgrund'])."',
			'".mysql_real_escape_string($ins['status'])."',
			CURRENT_TIMESTAMP,
			'".$logon->user->usertype."',
			'".$logon->user->userid."',
			1)
		";
		$insert->query($insertstatement);
		$insertid=mysql_insert_id();
		foreach($inskursearray as $stunde=>$kursid) {
			$insertkursestatement = "
				INSERT INTO `fehlzeitenkurse` (`fid` ,`kid`, `stunde`) VALUES (
				'".$insertid."',
				'".$kursid."',
				'".$stunde."'
				);
			";
			$insert->query($insertkursestatement);
		}
		foreach($ins['sid'] as $insschueler)
		{
			$insertschuelerstatement = "
				INSERT INTO `fehlzeitenschueler` (`fid`,`sid`) VALUES (
					'".$insertid."',
					'".$insschueler."'
				);
			";
			$insert->query($insertschuelerstatement);
		}
		header("Location: index.php?p=fz&v=".$insertid);
	}
} else {
	if($logon->user->usertype == 1) {
		if(isset($_GET['lk']) && is_numeric($_GET['lk']))
		{
			$form = lehrerform('','',$_GET['lk']);
			$newfztmpl->setdbloop("schuelerselected",$form["schuelerselected"]);
		}
		else
		{
			$form = lehrerform('','','');
		}
		$newfztmpl->setvar('neuefz',1);
	} elseif($logon->user->usertype == 0) {
		$form = schuelerform('','');
	} else {
		$logon->logout();
	}
	if($logon->user->usertype == 1)
	{
		$newfztmpl->setloop("kurseladenloop",$form["kursarray"]);
	}
	$newfztmpl->setdbloop("schuelerselect",$form["schuelerselect"]);
	$newfztmpl->setloop('stundenvm', $form["stundenvm"]);
	$newfztmpl->setloop('stundennm', $form["stundennm"]);
	$newfztmpl->setdbloop('status', $form["status"]);
}
$newfztmpl->pparse();
?>