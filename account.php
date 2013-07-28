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
$accountverwaltung = new vlibTemplate('account.html');
$accountverwaltung->setVar("uservn",$logon->user->uservn);
$accountverwaltung->setVar("usernn",$logon->user->usernn);
$accountverwaltung->setVar("useremail",$logon->user->useremail);
$accountverwaltung->setVar("saname",$logon->user->saname);
$accountverwaltung->setVar("slname",$logon->user->slname);

if(isset($_POST["acc"])) // nur für Lehrer
{
	$email = checkemail($_POST['useremail']);
	if($email)
	{
		$userupdatesql = new sql();
		
		if($logon->user->usertype == 1) 
		{
			if($_POST["sendemail"][1] == "on") 
			{
				$kurslehrersendemail = 1;
			} else {
				$kurslehrersendemail = 0;
			}
			if($_POST["sendemail"][2] == "on") 
			{
				$tutorsendemail = 1;
			} else {
				$tutorsendemail = 0;
			}
			
			$lehrerupdatesql = new sql();
			$userupdatestatement = "
				UPDATE `lehrer` SET 
				`lemail` = '".mysql_real_escape_string($email)."', 
				`lsendemail` = '".$kurslehrersendemail."', 
				`lsendemailtutor` = ".$tutorsendemail." WHERE `lehrer`.`lid` = ".$logon->user->userid."
			";
			$lehrerupdatesql->query($userupdatestatement);
		}		
	}
}

if(isset($_GET["add"]))
{
	if($logon->user->usertype == 0 && is_numeric($_GET["kid"]) && $conf['skurshinzufuegen'] === true)
	{
		$skurshinzufuegensql = new sql();
		$skurshinzufuegenstatement = "
			INSERT IGNORE INTO `besucht` (`sid`, `kid`) VALUES ('".$logon->user->userid."', '".mysql_real_escape_string($_GET["kid"])."');
		";
		$skurshinzufuegensql->query($skurshinzufuegenstatement);
	}
	header("Location: index.php?p=acc");
}

if(isset($_GET["del"]))
{
	if($logon->user->usertype == 0 && is_numeric($_GET["kid"]) && $rechte->skursloeschen === true)
	{
		$skursloeschensql = new sql();
		$skursloeschenstatement = "
			DELETE FROM `entschuldigungssystem`.`besucht` WHERE `besucht`.`sid` = '".$logon->user->userid."' AND `besucht`.`kid` = '".mysql_real_escape_string($_GET["kid"])."'
		";
		$skursloeschensql->query($skursloeschenstatement);
	}
	header("Location: index.php?p=acc");
}

if($logon->user->usertype == 1) // Lehrer
{
	$sendemailsql = new sql();
	$sendemailstatement = "
		Select
		  lehrer.lid,
		  lehrer.lsendemail,
		  lehrer.lsendemailtutor
		From
		  lehrer
		Where
		  lehrer.lid = ".$logon->user->userid."
	";
	$sendemailsql->query($sendemailstatement);
	$sendemailsql->fetch_obj();
	if($sendemailsql->result->lsendemail == 1) 
	{
		$accountverwaltung->setVar("lsendemail","1");	
	}
	if($sendemailsql->result->lsendemailtutor == 1) 
	{
		$accountverwaltung->setVar("lsendemailtutor","1");	
	}
}

if($logon->user->usertype == 1) { // Lehrer
	$accountverwaltung->setvar('usertype','1');
	
	$kursbelegung = "
		Select
		  kurs.kid,
		  kurs.knummer,
		  kurstypen.ktname,
		  kurstypen.ktkuerzel,
		  abijahrgang.aname
		From
		  lehrer Inner Join
		  kurs On lehrer.lid = kurs.lid Inner Join
		  abijahrgang On kurs.aid = abijahrgang.aid Inner Join
		  kurstypen On kurs.ktid = kurstypen.ktid
		Where
		  lehrer.lid = '".$logon->user->userid."'
	";
	$kursbelegungsql = new sql();
	$kursbelegungsql->query($kursbelegung);
	$accountverwaltung->setdbloop('kurse', $kursbelegungsql->query);
	
} 
elseif ($logon->user->usertype == 0) 
{
	$accountverwaltung->setvar('usertype','0');
	
	$kursbelegung = "
		Select
		  kurstypen.ktname,
		  kurstypen.ktkuerzel,
		  kurs.knummer,
		  kurs.kid,
		  lehrer.lname
		From
		  kurs Inner Join
		  besucht On kurs.kid = besucht.kid Inner Join
		  schueler On schueler.sid = besucht.sid Inner Join
		  kurstypen On kurstypen.ktid = kurs.ktid Left Join
		  lehrer On lehrer.lid = kurs.lid
		Where
		  schueler.sid = '".$logon->user->userid."'
	";
	$kursbelegungsql = new sql();
	$kursbelegungsql->query($kursbelegung);
	$accountverwaltung->setdbloop('kurse', $kursbelegungsql->query);
	
	if($conf['skurshinzufuegen'] === true)
	{
		$accountverwaltung->setVar("skurshinzufuegen",1);
		
		$kurshinzufuegensql = new sql();
		$kurshinzufuegenstatement = "
			Select
			  kurstypen.ktname,
			  kurstypen.ktkuerzel,
			  kurs.knummer,
			  kurs.kid,
			  lehrer.lname
			From
			  kurs Inner Join
			  kurstypen On kurstypen.ktid = kurs.ktid Left Join
			  lehrer On lehrer.lid = kurs.lid
			WHERE
				kurs.aid = '".$logon->user->said."'
		";
		$kurshinzufuegensql->query($kurshinzufuegenstatement);
		$accountverwaltung->setDBLoop("neuerkurs",$kurshinzufuegensql->query);
	}
	
	if($rechte->skursloeschen === true)
	{
		$accountverwaltung->setVar("skurseloeschen",1);
	}
} 
else 
{
	$logon->logout();
}
$accountverwaltung->pparse();
?>