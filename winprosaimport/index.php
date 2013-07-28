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
header("Content-Type: text/html; charset=utf-8");
include("includes.inc.php");

if(isset($_GET['s']) && is_numeric($_GET['s'])) {
	$step = $_GET['s'];
} elseif(isset($_POST["senden"])) {

} elseif(isset($_POST["schueler"])) {
	$step = 4;
} else {
	$step = 1;
}
$neuekursearray = array(); # array der neuen Kurse, welche erstellt werden müssen
$neueabijahrganarray = array(); # array der AbiJahrgänge, die in der Datei vertreten sind

$row = 1;
$handle = fopen ("schuelerdaten.csv","r");	// Datei zum Lesen öffnen
while ( ($zeile = fgetcsv ($handle, 1000000, ";")) !== FALSE ) // Daten werden aus der Datei Zeilenweise ausgelesen
{
    if($row == 1) # erste Zeile als Beschriftung der Spalten !
	{
		foreach($zeile as $spaltenid => $spaltenbezeichnung) {
			$spaltenbezeichnungen[$spaltenbezeichnung] = $spaltenid;
		}
	}
	else
	{
		$schuelername = ''; $schuelervorname= '';
		$schuelername = formatname($zeile[$spaltenbezeichnungen[$schuelerfelder["nachname"]]]); // Nachname des Schuelers
		$schuelervorname = $zeile[$spaltenbezeichnungen[$schuelerfelder["vorname"]]]; // Vorname des Schuelers
		
		$abijahrgangvalue = ''; $abijahrgangvalue = $zeile[$spaltenbezeichnungen[$schuelerfelder["abijahrgang"]]]; # Abi-Jahrgang des Schülers ermitteln
		$abijahrgangid = abijahrgang($abijahrgangvalue);
		
		$simportedid = $zeile[$spaltenbezeichnungen[$schuelerfelder["simportedid"]]];
		$idnr = $zeile[$spaltenbezeichnungen[$schuelerfelder["idnr"]]];
		$geburtsdatum = geburtsdatum($zeile[$spaltenbezeichnungen[$schuelerfelder["geburtsdatum"]]]);
		$tutor = tutor($zeile[$spaltenbezeichnungen[$schuelerfelder["tutor"]]]);
		$tutorvalue = $zeile[$spaltenbezeichnungen[$schuelerfelder["tutor"]]];
		$relivalue = $zeile[$spaltenbezeichnungen[$schuelerfelder["relivalue"]]];
		
		if(
			empty($simportedid) || 
			empty($geburtsdatum) || 
			empty($tutor) || 
			empty($schuelername) || 
			empty($schuelervorname) || 
			empty($abijahrgangvalue) ||
			!in_array($relivalue,$relikonfessionen)
		)
		{
			$schuelerdatenfehlerarray[] = array(
				"zeile" => $row,
				"fehler" => 'Schülerdaten konnten nicht alle gelesen werden',
				"schuelername" => $schuelername,
				"schuelervorname" => $schuelervorname,
				"abijahrgang" => $abijahrgangvalue,
				"abijahrgangid" => $abijahrgangid,
				"simportedid" => $simportedid,
				"idnr" => $idnr,
				"geburtsdatum" => $geburtsdatum,
				"tutor" => $tutor,
				"tutorvalue" => $tutorvalue,
				"relivalue" => $relivalue
			);
		}
		elseif(empty($abijahrgangid) && ($step == 3 || $step == 4))
		{
			$schuelerdatenfehlerarray[] = array(
				"zeile" => $row,
				"fehler" => 'Abijahrgang-ID des Schülers existiert noch nicht',
				"schuelername" => $schuelername,
				"schuelervorname" => $schuelervorname,
				"abijahrgang" => $abijahrgangvalue,
				"abijahrgangid" => $abijahrgangid,
				"simportedid" => $simportedid,
				"idnr" => $idnr,
				"geburtsdatum" => $geburtsdatum,
				"tutor" => $tutor,
				"tutorvalue" => $tutorvalue,
				"relivalue" => $relivalue
			);
		}
		else
		{
			if($step == (3 OR 4)) 
			{
				$neuerschueler[$row] = array(
					"schuelername" => $schuelername,
					"schuelervorname" => $schuelervorname,
					"abijahrgang" => $abijahrgangvalue,
					"abijahrgangid" => $abijahrgangid,
					"simportedid" => $simportedid,
					"idnr" => $idnr,
					"geburtsdatum" => $geburtsdatum,
					"tutor" => $tutor,
					"kurse" => array()
				);
			}
			if($step == 1) # Neue, zu erstellende Kurse ermitteln
			{
				if(!in_array($abijahrgangvalue,$neueabijahrganarray))
				{
					$neueabijahrganarray[] = $abijahrgangvalue; # Array mit allen AbiJährgängen der neuen Schüler
				}
			}	
			foreach($kurstypenarray as $kurstypid) # für jeden Kurstyp
			{
				$stundentyp = false;
				if(empty($relifelder[$kurstypid]) || $relivalue == $relifelder[$kurstypid])
				{
					foreach($winprosakursfelder[$kurstypid] as $winprosasemfeld) # prüfen, ob Schüler den Kurs 2 oder 4-Stündig hat
					{
						$stundenbelegungsvalue = $zeile[$spaltenbezeichnungen[$winprosasemfeld]];
						if(in_array($stundenbelegungsvalue,$vierstuendigebezeichnungen) && $kurstypstunden[$kurstypid] == "4") 
						{
							$stundentyp = 4; # -> Kurs ist 4-Stündig belegt
						} elseif(in_array($stundenbelegungsvalue,$zweistuendigebezeichnungen) && $kurstypstunden[$kurstypid] == "2") {
							$stundentyp = 2; # -> Kurs ist 2-Stündig belegt
						} 
						elseif
						(	in_array($stundenbelegungsvalue,$nichtbelegtbezeichnungen) OR
							in_array($stundenbelegungsvalue,$zweistuendigebezeichnungen) OR 
							in_array($stundenbelegungsvalue,$vierstuendigebezeichnungen)
						) 
						{
							
						} else {
							$datenfehlerarray[] = array(
								"zeile" => $row,
								"spalte" => $winprosasemfeld,
								"fehlervalue" => '>'.$stundenbelegungsvalue.'<',
								"schuelervorname" => $schuelervorname,
								"schuelername" => $schuelername
							);
						}
					}
				}
				if($stundentyp != false) 
				{
					foreach($winprosakursnummerfelder[$kurstypid] as $winprosapksemfeld) # Prüfen in welchem Kurs (Kursnummer) der Schüler ist -> somit wird die Anzahl der benötigten Kurse für einen Kurstyp ermittelt
					{
						$kursnummervalue = $zeile[$spaltenbezeichnungen[$winprosapksemfeld]];
						if($step == 1) 
						{
							if(is_numeric($kursnummervalue) && (!isset($neuekursearray[$kurstypid][$abijahrgangvalue]) || ($neuekursearray[$kurstypid][$abijahrgangvalue] < $kursnummervalue)))
							{
								$neuekursearray[$kurstypid][$abijahrgangvalue] = $kursnummervalue;
							}
						}
						if($step == (3 OR 4) && is_array($neuerschueler[$row]))
						{
							if(is_numeric($kursnummervalue) && $kursnummervalue > 0 && (!isset($neuerschueler[$row]["kurse"][$kurstypid])))
							{
								$kursselectstatement = "
									Select
									  kurs.kid,
									  Concat(kurstypen.ktname, ' (', kurstypen.ktkuerzel, kurs.knummer,
									  ') Abi-Jahr ', abijahrgang.aname, ' (', kurstypen.ktstunden,
									  ' Stunden/Woche)') As kursname
									From
									  kurs Inner Join
									  abijahrgang On kurs.aid = abijahrgang.aid Inner Join
									  kurstypen On kurs.ktid = kurstypen.ktid
									Where
									  kurs.knummer = ".$kursnummervalue." And
									  abijahrgang.aname = ".$abijahrgangvalue." AND
									  kurs.ktid = ".$kurstypid."
								";
								$kurseseletc = mysql_query($kursselectstatement);
								$resultcount = mysql_num_rows($kurseseletc);
								if($resultcount != 1) 
								{
									$kursselectfehlerarray[] = array(
										"zeile" => $row,
										"spalte" => $winprosapksemfeld,
										"sql" => $kursselectstatement,
										"fehler" => 'Kurs konnte nicht aus Datenbank gelesen werden',
										"schuelervorname" => $schuelervorname,
										"schuelername" => $schuelername
									);
								}
								else
								{
									$kursidobj = mysql_fetch_object($kurseseletc);
									$kursid = $kursidobj->kid;
									$kursname = $kursidobj->kursname;
									if(is_numeric($kursid)) 
									{
										$neuerschueler[$row]["kurse"][$kurstypid] = array(
											"kursid" => $kursid,
											"kursname" => $kursname
										);
									}
									else 
									{
										$kursselectfehlerarray[] = array(
											"zeile" => $row,
											"spalte" => $winprosapksemfeld,
											"sql" => $kursselectstatement,
											"fehler" => 'Kurs-ID wurde falsch ausgelesen',
											"kursid" => '>'.$kursid.'<',
											"schuelervorname" => $schuelervorname,
											"schuelername" => $schuelername
										);
									}
								}
							}
						}
					}
				}
			}
		}
	}
	$row++;
}

if($step == 1) {
	foreach($neuekursearray as $ktypid => $jahrearray) 
	{
		$tempjahresarray = array();
		foreach($jahrearray as $abijahrgang => $kursanzahl) 
		{
			$tempjahresarray[] = array (
				"aname" => $abijahrgang,
				"kursanzahl" => $kursanzahl,
				"ktid" => $ktypid
			);
		}
		$neuekursetmplarray[] = array(
			"ktid" => $ktypid,
			"ktname" => $kurstypname[$ktypid]["ktname"],
			"ktkuerzel" => $kurstypname[$ktypid]["ktkuerzel"],
			"ktstunden" => $kurstypstunden[$ktypid],
			"jahre" => $tempjahresarray
		);
	}
	foreach($neueabijahrganarray as $aname) {
		$neueabijahrgantemparray[] = array (
			"aname" => $aname
		);
	}
	$tmpl = new vlibTemplate('neuekurse.html');
	$tmpl->setLoop("neuekurse",$neuekursetmplarray);
	$tmpl->setLoop("datenfehler",$datenfehlerarray);
	$tmpl->setLoop("neueabijahrgang",$neueabijahrgantemparray);
	$tmpl->setLoop("schuelerdatenfehlerarray",$schuelerdatenfehlerarray);
	$tmpl->pparse();
}
if($step == 3) 
{
	$neueschuelertmpl=new vlibTemplate('neueschueler.html');
	$neueschueleranzahl = count($neuerschueler);
	$neueschuelertmpl->setVar("neueschueleranzahl",$neueschueleranzahl);
	$neueschuelertmpl->setLoop("neueschueler",$neuerschueler);
	$neueschuelertmpl->setLoop("datenfehler",$datenfehlerarray);
	$neueschuelertmpl->setLoop("schuelerdatenfehlerarray",$schuelerdatenfehlerarray);
	$neueschuelertmpl->pparse();
}

if($step == 4) 
{
	foreach($neuerschueler as $schuelerarray) {
		$schuelername = $schuelerarray["schuelername"];
		$schuelervorname = $schuelerarray["schuelervorname"];
		$abijahrgang = $schuelerarray["abijahrgang"];
		$abijahrgangid = $schuelerarray["abijahrgangid"];
		$simportedid = $schuelerarray["simportedid"];
		$idnr = $schuelerarray["idnr"];
		$geburtsdatum = $schuelerarray["geburtsdatum"];
		$tutor = $schuelerarray["tutor"];
		
		$schuelerupdatesid = ""; // schueler.sid
		
		$schuelerupdatesql = new sql(); // Wenn Schüler schon existiert, hole sid
		$schuelerupdatestatement = "
			Select
			  schueler.sid
			From
			  schueler
			Where
			  schueler.IDNR = '".$idnr."' And
			  schueler.aid = ".$abijahrgangid."
		";
		$schuelerupdatesql->query($schuelerupdatestatement);
		$schuelerupdatesql->fetch_obj();
		if(is_numeric($schuelerupdatesql->result->sid))
		{		
			$schuelerupdatesid = $schuelerupdatesql->result->sid;
		}
		else
		{
			$neuerschuelerinsertstatement = "
				INSERT INTO `schueler` (`sid`, `simportedid`, `sname`, `svorname`, `semail`, `aid`, `lid`, `IDNR`, `sendemail`, `sgeburtsdatum`) VALUES 
				(NULL, '".$simportedid."', '".$schuelername."', '".$schuelervorname."', NULL, '".$abijahrgangid."', '".$tutor."', '".$idnr."', NULL, '".$geburtsdatum."')
			";
			$neuerschuelerquery = mysql_query($neuerschuelerinsertstatement);
			if(!$neuerschuelerquery) 
			{ 
				echo 'Fehler: '.mysql_error()."<br>\n";
				echo $neuerschuelerinsertstatement."<br>\n";
			}
			else 
			{
				$schuelerupdatesid = mysql_insert_id();
			}
		}

		if(!empty($schuelerupdatesid))
		{
			foreach($schuelerarray["kurse"] as $kursvaluearray)
			{
				$besuchtinsert = "
					INSERT INTO `besucht` (`sid`, `kid`) VALUES ('".$schuelerupdatesid."', '".$kursvaluearray["kursid"]."');
				";
				@mysql_query($besuchtinsert);
			}
		}
		else {
			die("fehler");
		}
	}
}

if(isset($_POST["neuekurseanzahl"])) {
	foreach($_POST["abijahrgang"] as $abijahr) {
		if(is_numeric($abijahr) && $abijahr > 0 ) {
			$insertstatement = "
				INSERT INTO `abijahrgang` (
					`aid` ,
					`aname`
					)
					VALUES (
					NULL , '".$abijahr."'
					);
			";
			$query = mysql_query($insertstatement);
			$affectedrows = mysql_affected_rows();
			if($affectedrows != 1) {
				$insertfehlerarray[] = array(
					"abijahrgang" => $abijahr,
					"sql" => $insertstatement,
					"error" => mysql_error()
				);
			}
		}
	}
	foreach($_POST["neuekurseanzahl"] as $ktid => $jahrgangarray) {
		foreach($jahrgangarray as $jahrgang => $kursanzahl) {
			if(is_numeric($kursanzahl) && is_numeric($jahrgang) && $kursanzahl > 0) {
				for($i=1;$i<=$kursanzahl;$i++) {
					$neuerkursinsertstatement = "
						INSERT INTO `kurs` (`kid`, `ktid`, `knummer`, `aid`, `kversion`, `lid`) SELECT 
						NULL, '".$ktid."', '".$i."', abijahrgang.aid, '0', '0' FROM abijahrgang WHERE abijahrgang.aname = '".$jahrgang."'
					";
					$query = mysql_query($neuerkursinsertstatement);
					$affectedrows = mysql_affected_rows();
					if($affectedrows != 1) {
						$insertfehlerarray[] = array(
							"ktid" => $ktid,
							"jahrgang" => $jahrgang,
							"kursnummer" => $i,
							"sql" => $neuerkursinsertstatement,
							"error" => mysql_error()
						);
					}
				}
			}
		}
	}
	print_r($insertfehlerarray);
}

/*print_r($datenfehlerarray);
print_r($neuekursetmplarray);
print_r($neueabijahrganarray);*/
fclose ($handle);
?>