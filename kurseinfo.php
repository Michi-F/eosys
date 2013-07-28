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
$kurseinfo = new vlibTemplate('kurseinfo.html');
if($logon->user->usertype != 1) 
{	
	header("Location: index.php");
}
if(!isset($_GET['k']) || !is_numeric($_GET['k'])) 
{
	$kurseinfo->setVar('title','Fehler: Kurs konnte nicht gelesen werden.');
}
else
{
	$kursrechtesql = new sql();
	$kursechtestatement = "
		Select
			kurs.kid,
			lehrer.lid
		From
			kurs Inner Join			
			lehrer On lehrer.lid = kurs.lid
		Where
			kurs.kid = ".mysql_real_escape_string($_GET['k'])." AND
			lehrer.lid = '".$logon->user->userid."'
	";
	$kursrechtesql->query($kursechtestatement);
	if($kursrechtesql->num_rows() == 1 || $rechte->kursbearbeiten === true)
	{
		$kurseinfo->setVar("kursbearbeiten",1);
		if(isset($_GET["del"]))
		{
			$schuelerloeschen = $_GET["sid"];
			if(is_numeric($schuelerloeschen) && $logon->user->usertype == 1)
			{
				$schuelerloeschensql = new sql();
				$schuelerloeschenstatement = "
					DELETE FROM
						besucht 
					WHERE 
						besucht.kid = ".mysql_real_escape_string($_GET['k'])." 
						AND besucht.sid = ".mysql_real_escape_string($_GET["sid"])."
				";
				$schuelerloeschensql->query($schuelerloeschenstatement);
			}
			header("Location: index.php?p=ki&k=".$_GET['k']);
		}
		if(isset($_GET["add"]))
		{		
			$schueleradd = $_GET["sid"];
			if(is_numeric($schueleradd) && $logon->user->usertype == 1)
			{
				$schueleraddsql = new sql();
				$schueleraddstatement = "
					INSERT IGNORE Into besucht (kid,sid) VALUES (
					'".mysql_real_escape_string($_GET['k'])."','".mysql_real_escape_string($_GET["sid"])."');
				";			
				$schueleraddsql->query($schueleraddstatement);
			}
			header("Location: index.php?p=ki&k=".$_GET['k']);
		}
	}
	$kurseinfo->setVar('title','Kursinformation');
	$kursdaten = new sql();	
	$kursdatenstatement = "
		Select
			kurstypen.ktname,
			kurstypen.ktkuerzel,
			kurstypen.ktstunden,
			abijahrgang.aid,
			kurs.knummer,
			abijahrgang.aname,
			lehrer.lkuerzel,
			lehrer.lname 
		From
			kurs Inner Join
			kurstypen On kurs.ktid = kurstypen.ktid Inner Join
			abijahrgang On abijahrgang.aid = kurs.aid Left Outer Join
			lehrer On lehrer.lid = kurs.lid
		Where
			kurs.kid = ".mysql_real_escape_string($_GET['k'])."
	";	
	$kursdaten->query($kursdatenstatement);	$kurseinfo->setDbLoop("kurs",$kursdaten->query);
	$kurseschueler = new sql();
	$kurseschuelerstatent = "
		Select
			schueler.sid,
			schueler.sname,
			schueler.svorname,
			kurs.kid
		From
			kurs Inner Join	
			besucht On besucht.kid = kurs.kid Inner Join
			schueler On schueler.sid = besucht.sid
		Where
			kurs.kid = ".mysql_real_escape_string($_GET['k'])."
		Order By
			schueler.sname, schueler.svorname, schueler.sid
	";
	$kurseschueler->query($kurseschuelerstatent);
	$kurseinfo->setDbLoop("schueler",$kurseschueler->query);
	$kurseschueleradd = new sql();
	$kurseschueleraddstatement = 
	"
		Select
			schueler.sname,
			schueler.svorname,
			schueler.sid
		From
			kurs Inner Join
			schueler On kurs.aid = schueler.aid
		Where
			kurs.kid = ".mysql_real_escape_string($_GET['k'])."	
	";
	$kurseschueleradd->query($kurseschueleraddstatement);
	$kurseinfo->setDbLoop("schueleradd",$kurseschueleradd->query);
}
$kurseinfo->pparse();
?>