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
$uebersichtfztmpl = new vlibTemplate('uebersichtfz.html');

function ueschueler($whereid,$fstatusfilter,$kfilter,$filtervondatum,$filternachschueler) {
	if($kfilter > 0)
	{
		$kursfilter = "And kurs.kid = '".$kfilter."'";
	}
	else
	{
		$kursfilter = "";
	}
	if($filternachschueler > 0)
	{
		$filterschueler = "And schueler.sid = '".$filternachschueler."'";
	}
	else
	{
		$filterschueler = "";
	}
	
	global $logon;
	$uebersichtfz = new sql();
	$uebersichtfzstatement = "
		Select
		  fehlzeit.fid,
		  fehlzeit.fsid,
		  DATE_FORMAT(fehlzeit.ffehldatum, '%d.%m.%Y') AS ffehldatum,
		  fehlzeit.fgrund,
		  fehlzeitstatus.fsname,
		  fehlzeitstatus.fstyp,
		  Convert(Group_Concat(Distinct fehlzeitenkurse.stunde, '.Stunde:',kurstypen.ktname, If(kurs.kid != 1, Concat(' (', kurstypen.ktkuerzel,kurs.knummer, ')'), '') Order By fehlzeitenkurse.stunde Separator '<br>') Using latin1) As kurse,
		  IF(Count(DISTINCT schueler.sid) > 1, concat(Count(DISTINCT schueler.sid),' Schüler/innen'), Concat(schueler.sname, ', ', schueler.svorname)) as schuelername,
		  IF(fehlzeit.utype = ".$logon->user->usertype." AND fehlzeit.userid = ".$logon->user->userid." AND fehlzeitstatus.fstyp = 1, '1','0') as loeschen
		From
		  fehlzeit Inner Join
		  fehlzeitstatus On fehlzeitstatus.fsid = fehlzeit.fsid Inner Join
		  fehlzeitenkurse On fehlzeit.fid = fehlzeitenkurse.fid Inner Join
		  kurs On fehlzeitenkurse.kid = kurs.kid Inner Join
		  kurstypen On kurstypen.ktid = kurs.ktid Inner Join
		  abijahrgang On abijahrgang.aid = kurs.aid Inner Join
		  fehlzeitenschueler On fehlzeitenschueler.fid = fehlzeit.fid Inner Join
		  schueler On schueler.sid = fehlzeitenschueler.sid Inner Join
		  lehrer On lehrer.lid = schueler.lid
		Where
		  (fehlzeit.fsid IN(".$fstatusfilter.")) 
		  ".$whereid."
		  ".$kursfilter."
		  ".$filterschueler."
		  AND fehlzeit.ffehldatum > '".$filtervondatum."'
		  AND fehlzeit.fversion = (
			Select
			  Max(fehlzeitversiontabelle.fversion)
			From
			  fehlzeit AS fehlzeitversiontabelle
			Where
			  fehlzeitversiontabelle.fid = fehlzeit.fid
		  )
		Group By
		  fehlzeit.fid
		Order By
		  `fehlzeit`.`ffehldatum` DESC
	";
	$uebersichtfz->query($uebersichtfzstatement);
	return $uebersichtfz->query;
}
function uelehrer($lfilter,$fstatusfilter,$kfilter,$filtervondatum,$filternachschueler){
	global $logon;
	global $rechte;
	if($rechte->allefzloeschen)
	{
		$adminloeschen = "1=1 OR";
	}
	else
	{
		$adminloeschen = "";
	}
	if($lfilter > 0) // Kurslehrer Übersicht
	{
		$lehrerfilter = "And (kurs1.lid = '".$lfilter."' Or kurs1.lid = '1') And kurs.lid = '".$lfilter."'";
	}
	else // Admin Übersicht
	{
		$lehrerfilter = "";
	}
	if($kfilter > 0)
	{
		$kursfilter = "And kurs1.kid = '".$kfilter."'";
	}
	else
	{
		$kursfilter = "";
	}
	if($filternachschueler > 0)
	{
		$filterschueler = "And schueler.sid = '".$filternachschueler."'";
	}
	else
	{
		$filterschueler = "";
	}

	$uebersichtfz = new sql();
	$uebersichtfzstatement = "
		Select
		  fehlzeit.fid,
		  fehlzeit.fsid,
		  Date_Format(fehlzeit.ffehldatum, '%d.%m.%Y') As ffehldatum,
		  IF(Count(DISTINCT schueler.sid) > 1, concat(Count(DISTINCT schueler.sid),' Schüler/innen'), Concat(schueler.sname, ', ', schueler.svorname)) as schuelername,
		  Convert(Group_Concat(Distinct fehlzeitenkurse.stunde, '.Stunde:',kurstypen.ktname, If(kurs1.kid != 1, Concat(' (', kurstypen.ktkuerzel,kurs1.knummer, ')'), '') Order By fehlzeitenkurse.stunde Separator '<br>') Using latin1) As kurse,
		  fehlzeitstatus.fsname,
		  IF(Count(DISTINCT schueler.sid) > 1, '', lehrer.lname) as lehrername,
		  IF(".$adminloeschen." (fehlzeit.utype = ".$logon->user->usertype." AND fehlzeit.userid = ".$logon->user->userid." AND fehlzeit.fsid != 7), '1','0') as loeschen
		From
		  kurs Inner Join
		  besucht On kurs.kid = besucht.kid Inner Join
		  schueler On besucht.sid = schueler.sid Inner Join
		  fehlzeitenschueler On schueler.sid = fehlzeitenschueler.sid Inner Join
		  fehlzeit On fehlzeitenschueler.fid = fehlzeit.fid Inner Join
		  fehlzeitenkurse On fehlzeit.fid = fehlzeitenkurse.fid Inner Join
		  kurs kurs1 On fehlzeitenkurse.kid = kurs1.kid Inner Join
		  kurstypen On kurs1.ktid = kurstypen.ktid Inner Join
		  fehlzeitstatus On fehlzeit.fsid = fehlzeitstatus.fsid Inner Join
		  lehrer On schueler.lid = lehrer.lid
		Where
		  (fehlzeit.fsid IN(".$fstatusfilter."))
		  ".$lehrerfilter."
		  ".$kursfilter."
		  ".$filterschueler."
		  AND fehlzeit.ffehldatum > '".$filtervondatum."'
		  AND fehlzeit.fversion = (
			Select
			  Max(fehlzeitversiontabelle.fversion)
			From
			  fehlzeit AS fehlzeitversiontabelle
			Where
			  fehlzeitversiontabelle.fid = fehlzeit.fid
		  )
		Group By
		  fehlzeit.fid
		Order By
		  fehlzeit.ffehldatum Desc, fehlzeit.fid DESC
	";
	$uebersichtfz->query($uebersichtfzstatement);
	return $uebersichtfz->query;
}
function filterkurs($whereid,$schuelerjoin) {
	$filterkurse = new sql();
	$filterkursestatement = "
		Select
		  kurstypen.ktname,
		  kurstypen.ktkuerzel,
		  kurs.knummer,
		  kurs.kid,
		  abijahrgang.aname,
		  IF(".intval($_GET['f'])."=kurs.kid,'1','0') as selected
		From
		  kurs Inner Join
		  kurstypen On kurs.ktid = kurstypen.ktid Inner Join
		  abijahrgang On kurs.aid = abijahrgang.aid ".$schuelerjoin."
		Where
		  ".$whereid."
		GROUP BY
		  kurs.kid
		ORDER BY
		  `kurs`.`aid`,`kurstypen`.`ktname`, kurs.knummer
	";
	$filterkurse->query($filterkursestatement);
	return $filterkurse;
}
function filterschueler() 
{
	global $_GET;
	global $logon;
	global $rechte;
	
	$lehrerjoin = "";
	if(isset($_GET['a']) && $rechte->allefzlesen) { // Admin
		$schuelerfilter = '1=1';
	} elseif($logon->user->usertype == 1) {
		if(isset($_GET['t'])) 
		{
			$schuelerfilter = "schueler.lid = ".$logon->user->userid."";
		} else 
		{
			$schuelerfilter = "kurs.lid = ".$logon->user->userid."";
			$lehrerjoin = 'Inner join besucht On besucht.sid = schueler.sid Inner join kurs On kurs.kid = besucht.kid';
		}
	}
	$schuelerfiltersql = new sql();
	$schuelerfilterstatement = "
		Select
		  schueler.sid,
		  schueler.sname,
		  schueler.svorname,
		  IF(".intval($_GET['sid'])."=schueler.sid,'1','0') as selected
		FROM
		  schueler
		  ".$lehrerjoin."
		WHERE
		  ".$schuelerfilter."
		Order By
		  schueler.sname, schueler.svorname, schueler.aid, schueler.sid
	";
	$schuelerfiltersql->query($schuelerfilterstatement);
	return $schuelerfiltersql->query;
}

if(isset($_GET['fs'])) {
	$filterstatuexplodesarray = explode(',',$_GET['fs']);
	foreach($filterstatuexplodesarray as $kid) {
		$filterstatusarray[] = intval($kid);
	}
	$filterstatus = implode(',',$filterstatusarray);
} else {
	$filterstatus = false;
}
$filterstatussql = new sql();
$filterstatusstatement = "
	Select
	  fehlzeitstatus.fsid,
	  fehlzeitstatus.fsname,
	  IF('".mysql_real_escape_string($filterstatus)."'=fsid,'selected','') as selected
	From
	  fehlzeitstatus
	ORDER BY `fehlzeitstatus`.`fstyp`,`fehlzeitstatus`.`fsid`
";
$filterstatussql->query($filterstatusstatement);
$uebersichtfztmpl->setdbloop('filterstatusloop', $filterstatussql->query);

if(isset($_GET['a']) && $rechte->allefzlesen) { // Admin
	$kursfilter = '1=1';
} elseif($logon->user->usertype == 1) {
	if(isset($_GET['t'])) {
		$kursfilter = "schueler.lid = ".$logon->user->userid." Or kurs.kid = 1";
		$schuelerjoin = "
		  Left Join
		  besucht On kurs.kid = besucht.kid Left Join
		  schueler On besucht.sid = schueler.sid
		";
	} else {
		$kursfilter = "kurs.lid = ".$logon->user->userid."";
		$schuelerjoin = '';
	}
} elseif($logon->user->usertype == 0) {
	$kursfilter = "besucht.sid = ".$logon->user->userid." Or kurs.kid = 1";
	$schuelerjoin = 'Left Join
		  besucht On besucht.kid = kurs.kid';
} else {
	$logon->logout();
}
$filterkursselect = filterkurs($kursfilter,$schuelerjoin);
$uebersichtfztmpl->setdbloop('filterkurse', $filterkursselect->query);

if($logon->user->usertype == 1)
{
	$filterschueler = filterschueler();
	$uebersichtfztmpl->setdbloop('filterschuelerloop', $filterschueler);
	if(isset($_GET['sid']) && is_numeric($_GET['sid'])) {
		$filternachschueler = intval($_GET['sid']);
	} else {
		$filternachschueler = '';
	}
}

if(isset($_GET['f']) && is_numeric($_GET['f'])) {
	$filterkurs = intval($_GET['f']);
} else {
	$filterkurs = '';
}
if(!isset($_GET['fvdatum']) || !datum($_GET['fvdatum'])) {
	if(isset($_GET['t']) && isset($_GET['fs']) && $_GET['fs'] == '1,5') {
		$filtervondatum = date("d.m.Y",strtotime('-2 years'));
		$filtervondatumsql = date("Y-m-d",strtotime('-2 years'));
	} else {
		$filtervondatum = date("d.m.Y",strtotime('-10 weeks'));
		$filtervondatumsql = date("Y-m-d",strtotime('-10 weeks'));
	}	
} else {
	$filtervondatumsql = datum($_GET['fvdatum']);
	$filtervondatum = date("d.m.Y",strtotime($filtervondatumsql));
}
$uebersichtfztmpl->setvar('filterdatum', $filtervondatum);

if($logon->user->usertype == 1) {
	$uebersichtfztmpl->setvar("usertype","1");
	if(!$filterstatus) {
		$filterstatus = '1,2,3,4,5,6,8,9';
	}
	
	if(isset($_GET['t'])) {
		$lehrerform = ueschueler("AND (schueler.lid = '".$logon->user->userid."')",$filterstatus,$filterkurs,$filtervondatumsql,$filternachschueler);
	} else {
		if(isset($_GET['a']) && $rechte->allefzlesen) { // Admin
			$lehrerfilter = '';
			$uebersichtfztmpl->setVar("ueadmin",1); // Übersichtfzadmin
		} else {
			$lehrerfilter = $logon->user->userid;
		}
		$lehrerform = uelehrer($lehrerfilter,$filterstatus,$filterkurs,$filtervondatumsql,$filternachschueler);
	}
	$uebersichtfztmpl->setdbloop('kurse', $lehrerform);
} elseif($logon->user->usertype == 0) {
	$uebersichtfztmpl->setvar("usertype","0");
	if(!$filterstatus) {
		if(isset($_GET['ue'])) {
			$filterstatus = '4';
		} else {
			$filterstatus = '1,2,3,5,6,8,9';
		}
	}
	$schuelerform = ueschueler("AND (schueler.sid = '".$logon->user->userid."')",$filterstatus,$filterkurs,$filtervondatumsql,0);
	$uebersichtfztmpl->setdbloop('kurse',$schuelerform);
} else {
	//$logon->logout();
}
$uebersichtfztmpl->pparse();
?>