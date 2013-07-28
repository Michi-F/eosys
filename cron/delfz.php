#!/usr/bin/php -f
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
?>
<br><br>
Bei Folgenden Fehlzeiten ist etwas schiefgelaufen. Es wurden 2 Fehlzeiten f&uuml;r den gleichen
Sch&uuml;ler in der Datenbank gefunden, aber die eingetragenen Kurse stimmen nicht &uuml;berein.
<br>
Dabei ist <br>
- FZ1 = die "Entschuldigte" - Fehlzeit und <br>
- FZ2 die von einem Lehrer eingetragene "Entschuldigung liegt nicht vor" - Fehlzeit
<br><br><br>
<table border="1" cellpadding="20">
	<tr>
		<th>Sch&uuml;lerid</th>
		<th>FZ1 ID</th>
		<th>FZ2 ID</th>
		<th>Kurse FZ1</th>
		<th>Kurse FZ2</th>
		<th>Datum FZ1</th>
		<th>Datum FZ2</th>
	</tr>
<?php
// Diese Datei löscht doppelte Fehlzeiten.
// Bsp: Der Schüler hat es versäumt sein Fehlen einzutragen.
// Der Lehrer trägt nun ein, dass der Schüler A am Datum X bei ihm gefehlt hat (-> "Entschuldigung liegt nicht vor")
// Der Schüler A trägt sein Fehlen für das Datum X nach und wird entschuldigt oder ein Fachlehrer trägt ihn als entschuldigt ein.
// Nun wird die Fehlzeit des Lehrers ("Entschuldigung liegt nicht vor") auf "Gelöscht von System" gesetzt

include('../vlib/vlibTemplate.php');
include("../config.php");
include("../mysql.class.php");

function kursearray($fid)
{
	$kursearray = array();
	
	$kursesql = new sql();
	$kursestatement = "
		Select
		  fehlzeit.fid,
		  fehlzeitenkurse.kid,
		  fehlzeitenkurse.stunde
		From
		  fehlzeit Inner Join
		  fehlzeitenkurse On fehlzeit.fid = fehlzeitenkurse.fid
		Where
		  fehlzeit.fid = '".mysql_real_escape_string($fid)."'
		Order By
		  fehlzeitenkurse.stunde ASC
	";
	$kursesql->query($kursestatement);
	while($kursesql->fetch_obj())
	{
		$kursearray[$kursesql->result->stunde] = $kursesql->result->kid;
	}
	return $kursearray;
}

$doppeltefehlzeiten = array();

$neuefehlzeitensql = new sql();
$neuefehlzeitenstatement = "
	Select
	  fehlzeitenschueler.sid,
	  fehlzeit.fid,
	  fehlzeit1.fid As fid1,
	  fehlzeit.fsid,
	  fehlzeit1.fsid As fsid1,
	  fehlzeit.ffehldatum,
	  fehlzeit1.ffehldatum As ffehldatum1
	From
	  fehlzeit Inner Join
	  fehlzeitenschueler On fehlzeit.fid = fehlzeitenschueler.fid Inner Join
	  fehlzeitenschueler fehlzeitenschueler1 On fehlzeitenschueler.sid =
		fehlzeitenschueler1.sid Inner Join
	  fehlzeit fehlzeit1 On fehlzeitenschueler1.fid = fehlzeit1.fid And
		fehlzeit.ffehldatum = fehlzeit1.ffehldatum
	Where
	  fehlzeit.fid != fehlzeit1.fid And
	  (fehlzeit.fsid In (3,6,8,9)) And
	  fehlzeit1.fsid In (4) And
	  (fehlzeit.fversion = (Select
		Max(fehlzeitversiontabelle.fversion)
	  From
		fehlzeit As fehlzeitversiontabelle
	  Where
		fehlzeitversiontabelle.fid = fehlzeit.fid)) And
	  (fehlzeit1.fversion = (Select
		Max(fehlzeitversiontabelle2.fversion)
	  From
		fehlzeit As fehlzeitversiontabelle2
	  Where
		fehlzeitversiontabelle2.fid = fehlzeit1.fid))
	Order By
	  fehlzeit.ffehldatum Desc
";
$neuefehlzeitensql->query($neuefehlzeitenstatement);
while($neuefehlzeitensql->fetch_obj())
{
	$doppeltefehlzeiten[] = array(
		"sid"=>$neuefehlzeitensql->result->sid,
		"fid"=>$neuefehlzeitensql->result->fid, // ID der Fehlzeit Entschuldigt (mit Attest / durch Fachlehrer)
		"fid1"=>$neuefehlzeitensql->result->fid1, // ID der Fehlzeit "Entschuldigung liegt nicht vor"
		"fsid"=>$neuefehlzeitensql->result->fsid, // Entschuldigt (mit Attest / durch Fachlehrer)
		"fsid1"=>$neuefehlzeitensql->result->fsid1, // "Entschuldigung liegt nicht vor"
		"ffehldatum"=>$neuefehlzeitensql->result->ffehldatum, // gleich wie ffehldatum1
		"ffehldatum1"=>$neuefehlzeitensql->result->ffehldatum1 // gleich wie ffehldatum
	);
}

// Prüfen, ob die Kurse, die mit der "Entschuldigung liegt nicht vor"-Fehlzeit verknüpft sind auch mit der anderen Fehlzeit in derselben Stunde verknüpft sind
foreach($doppeltefehlzeiten as $doppeltefehlzeit)
{
	$liegtnichtvorfzkurse = kursearray($doppeltefehlzeit["fid1"]);
	$richtigefzkurse = kursearray($doppeltefehlzeit["fid"]);
	$allekursevorhanden = true;
	
	foreach($liegtnichtvorfzkurse as $lnvstid => $lnvkid) // stunde => kursid
	{
		if
		(
			!array_key_exists($lnvstid, $richtigefzkurse) || 
			($richtigefzkurse[$lnvstid] != $lnvkid && $richtigefzkurse[$lnvstid] != 1) // 1 wg. Allgemeinen Kurs ("Entschuldigt durch Fachlehrer")
		)
		{
			$allekursevorhanden = false;
		}
	}
	
	if($allekursevorhanden === false)
	{
		echo
		'
		<tr>
			<td>'.$doppeltefehlzeit["sid"].'</td>
			<td><a href="../index.php?p=fz&v='.$doppeltefehlzeit["fid"].'">'.$doppeltefehlzeit["fid"].'</td>
			<td><a href="../index.php?p=fz&v='.$doppeltefehlzeit["fid1"].'">'.$doppeltefehlzeit["fid1"].'</td>
			<td>
			';
			foreach($richtigefzkurse as $stunde => $kursid)
			{
				echo $stunde.".Stunde => Kurs ".$kursid."<br>";
			}
			echo '</td>
			<td>';
			foreach($liegtnichtvorfzkurse as $stunde => $kursid)
			{
				echo $stunde.".Stunde => Kurs ".$kursid."<br>";
			}
			echo "</td>
			<td>".$doppeltefehlzeit["ffehldatum"]."</td>
			<td>".$doppeltefehlzeit["ffehldatum1"]."</td>
		</tr>
		";
		/*var_dump($doppeltefehlzeit);
		var_dump($liegtnichtvorfzkurse);
		var_dump($richtigefzkurse);
		echo "\n\n\n\n\n\n\n\n\n";*/
	}
	elseif(is_numeric($doppeltefehlzeit["fid1"]))
	{
		$update = new sql();
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
			  10,
			  fehlzeit.feintragedatum,
			  CURRENT_TIMESTAMP,
			  1,
			  1,
			  fehlzeit.fversion + 1
			From
			  fehlzeit
			Where
			  fehlzeit.fid = ".mysql_real_escape_string($doppeltefehlzeit["fid1"])."
		";
		/*var_dump($doppeltefehlzeit);
		var_dump($liegtnichtvorfzkurse);
		var_dump($richtigefzkurse);
		echo "\n\n\n\n\n\n\n\n\n";*/
		//echo $updatestatement;
		$update->query($updatestatement);
	}
	else
	{
		// ?? solltte nie auftreten.
		die("Fehler. Irgendetwas stimmt nicht ! Bitte an den Hersteller wenden.");
	}
}
?>
</table>