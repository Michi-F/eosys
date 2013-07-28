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
class rechte
{
	var $adminfunktionen;
	var $allefzlesen;
	var $allefzloeschen;
	var $lehrerbearbeiten;
	var $schuelerbearbeiten;
	var $kursbearbeiten;
	var $skursloeschen; // Dürfen Schüler falsche Kurse selbst löschen ?  siehe Wert in config !
	var $skurshinzufuegen; // Dürfen Schüler falsche Kurse selbst hinzufuegen ?  siehe Wert in config !
	
	function rechte() {
		global $logon;
		global $conf;
		
		// zuerst alles auf false setzen
		$this->adminfunktionen = false; // 1
		$this->allefzlesen = false;	// 2
		$this->allefzloeschen = false; // 6
		$this->lehrerbearbeiten = false; // 3
		$this->schuelerbearbeiten = false; // 4
		$this->kursbearbeiten = false; // 5
		$this->skursloeschen = false;
		$this->skurshinzufuegen = false;

		// Schüler-spezifische Rechte:
		if($conf['skursloeschen'] === true) 
		{
			$this->skursloeschen = true;
		}		
		if($conf['skurshinzufuegen'] == 1) 
		{
			$this->skurshinzufuegen = true;
		}
		if($logon->user->usertype == 0)
		{
			return true;
		}
		// Ende Schüler-spez. Rechte -> return true -> rest wird nicht ausgeführt
		
		// Anfanng Lehrer-spez. Rechte		
		$rechtesql = new sql();
		$rechtestatement = "
			Select
			  userrechte.rid,
			  rechte.rname
			From
			  rechte Inner Join
			  userrechte On userrechte.rid = rechte.rid
			Where
			  userrechte.lid = ".$logon->user->userid."
		";
		$rechtesql->query($rechtestatement);
		while($rechtesql->fetch_obj()) {
			$rechtearray[] = $rechtesql->result->rid;
		}
		if(count($rechtearray) == 0) {
			return true;
		}
		if(in_array('1',$rechtearray)) {
			$this->adminfunktionen = true;
		}
		if(in_array('2',$rechtearray)) {
			$this->allefzlesen = true;
		}
		if(in_array('3',$rechtearray)) {
			$this->lehrerbearbeiten = true;
		}
		if(in_array('4',$rechtearray)) {
			$this->schuelerbearbeiten = true;
		}
		if(in_array('5',$rechtearray)) {
			$this->kursbearbeiten = true;
		}
		if(in_array('6',$rechtearray)) {
			$this->allefzloeschen = true;
		}
		return true;
		// Ende Lehrer-spez. Rechte
	}
}
class fzrechte
{
	var $fzlesen;
	var $fzentschuldigen;
	var $fzloeschen;
	
	function fzrechte($fid){
		global $logon;
		global $rechte;
		$this->fzlesen = false;
		$this->fzentschuldigen = false;
		$this->fzloeschen = false;
		
		if(!is_numeric($fid)) {
			return false;
		}
		$fehlzeit = new sql();
		$fehlzeitstatement = '
			Select
			  fehlzeit.utype,
			  fehlzeit.userid,
			  fehlzeitstatus.fstyp
			From
			  fehlzeit Inner Join
			  fehlzeitstatus On fehlzeitstatus.fsid = fehlzeit.fsid
			Where
			  fehlzeit.fid = '.mysql_real_escape_string($fid).'
			  AND fehlzeit.fversion = (
				Select
				  Max(fehlzeitversiontabelle.fversion)
				From
				  fehlzeit AS fehlzeitversiontabelle
				Where
				  fehlzeitversiontabelle.fid = fehlzeit.fid
			  )
		';
		$fehlzeit->query($fehlzeitstatement);
		$fehlzeit->fetch_obj();
		
		$fehlzeitschueler = array();
		$fehlzeitschuelersql = new sql();
		$fehlzeitschuelerstatement = 
		"
			Select
			  fehlzeitenschueler.sid,
			  schueler.lid
			From
			  fehlzeitenschueler Inner Join
			  schueler On schueler.sid = fehlzeitenschueler.sid
			Where
				fehlzeitenschueler.fid = '".mysql_real_escape_string($fid)."'
		";
		$fehlzeitschuelersql->query($fehlzeitschuelerstatement);
		while($schueler = $fehlzeitschuelersql->fetch_obj())
		{
			$fehlzeitschueler[$schueler->sid] = $schueler;
		}
		if
		(
			array_key_exists($logon->user->userid, $fehlzeitschueler) &&
			($fehlzeitschueler[$logon->user->userid]->sid == $logon->user->userid) && 
			$logon->user->usertype == 0
		)
		{
			$this->fzlesen = true;
		}
		
		if($logon->user->usertype == 1)
		{
			// Tutor: Fehltzeit entschuldigen
			$fehlzeitistutor = false;
			foreach($fehlzeitschueler as $fehlzeitschuelerobj)
			{
				if($fehlzeitschuelerobj->lid == $logon->user->userid)
				{
					$fehlzeitistutor = true;
				}
			}
			if(count($fehlzeitschueler) == 1 && $fehlzeitistutor === true) // Wenn für die Fehlzeit nur 1 Schüler eingetragen ist und der angemeldete Lehrer der Tutor dieses Schülers ist
			{
				$this->fzlesen = true;
				$this->fzentschuldigen = true;
			}
		}
		if($rechte->allefzloeschen || (($fehlzeit->result->userid == $logon->user->userid) && $fehlzeit->result->utype == $logon->user->usertype)) {
			if($logon->user->usertype == 1) 
			{
				$this->fzloeschen = true;
			}
			elseif($logon->user->usertype == 0)
			{
				if($fehlzeit->result->fstyp == "1")
				{
					$this->fzloeschen = true;
				}
			}
		}
		if($rechte->allefzlesen) 
		{
			$this->fzlesen = true;
		}
		
		if($logon->user->usertype == 1) {
			$berechtigtelehrer = new sql();
			$berechtigtelehrerstatement = '
				Select
				  kurs.lid
				From
				  fehlzeitenkurse Inner Join
				  kurs On fehlzeitenkurse.kid = kurs.kid
				Where
				  fehlzeitenkurse.fid = '.mysql_real_escape_string($fid).'
			';
			$berechtigtelehrer->query($berechtigtelehrerstatement);
			while($array = $berechtigtelehrer->fetch_obj()) {
				$berechtigtelehrerarray[] = $array->lid;
			}
			if(!empty($berechtigtelehrerarray)) {
				$berechtigtelehrerarr = array_unique($berechtigtelehrerarray);
				if(in_array($logon->user->userid,$berechtigtelehrerarr) || in_array(1,$berechtigtelehrerarr)) {
					$this->fzlesen = true;
				}
			}
		}
		return true;
	}
}
?>