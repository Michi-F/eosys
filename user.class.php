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
class user extends Exception {
	public $ok;
	public $usertype;
	public $userid;
	public $userimportedid;
	public $uservn;
	public $usernn;
	public $useremail;
	public $said;
	public $saname;
	public $slid;
	public $slname;
	public $sidnr;
	public $lkuerzel;
	
	function user($importedid,$utype) {
		$this->ok = false;
		if(!empty($importedid) && is_numeric($utype)) {
			if($utype == 0) {
				$userabfrage = new sql();
				$userabfragestatement = "
					Select
					  schueler.sid,
					  schueler.simportedid,
					  schueler.sname,
					  schueler.svorname,
					  schueler.semail,
					  schueler.aid,
					  abijahrgang.aname,
					  schueler.lid,
					  lehrer.lname,
					  schueler.IDNR,
					  schueler.sendemail
					From
					  schueler Inner Join
					  abijahrgang On schueler.aid = abijahrgang.aid Inner Join
					  lehrer On schueler.lid = lehrer.lid
					Where
					  schueler.simportedid = '".mysql_real_escape_string($importedid)."'
					  AND schueler.sstatus = 1
				";
				$userabfrage->query($userabfragestatement);
				if($userabfrage->num_rows() == 1)
				{
					$userarray = $userabfrage->fetch_obj();
					if(!$userarray) {
						throw new Exception('Benutzer konnte aus der lokalen Datenbank nicht ausgelesen werden.',112);
						return false;
					} else {
						$this->usertype = $utype;
						$this->userid = $userarray->sid;
						$this->userimportedid = $userarray->simportedid;
						$this->uservn = $userarray->svorname;
						$this->usernn = $userarray->sname;
						$this->useremail = $userarray->semail;						
						$this->said = $userarray->aid;
						$this->saname = $userarray->aname;
						$this->slid = $userarray->lid;
						$this->slname = $userarray->lname;
						$this->sidnr = $userarray->IDNR;
						$this->lkuerzel = false;
						$this->ok = true;
						return true;
					}
				} else {
					throw new Exception('Benutzer wurde in der lokalen Datenbank nicht gefunden.',111);
					return false;
				}
			} elseif ($utype == 1) {
				$userabfrage = new sql();
				$userabfrage->query("SELECT lid,limportedid,lkuerzel,lname,lvorname,lemail,lsendemail FROM `lehrer` WHERE `limportedid` = '".mysql_real_escape_string($importedid)."'");
				if($userabfrage->num_rows() == 1)
				{
					$userarray = $userabfrage->fetch_obj();
					if(!$userarray) {
						throw new Exception('Benutzer konnte aus der lokalen Datenbank nicht ausgelesen werden.',112);
						return false;
					} else {
						$this->usertype = $utype;
						$this->userid = $userarray->lid;
						$this->userimportedid = $userarray->limportedid;
						$this->uservn = $userarray->lvorname;
						$this->usernn = $userarray->lname;
						$this->useremail = $userarray->lemail;
						$this->said = false;
						$this->saname = false;
						$this->slid = false;
						$this->slname = false;
						$this->sidnr = false;
						$this->lkuerzel = $userarray->lkuerzel;
						$this->ok = true;
						return true;
					}
				} else {
					throw new Exception('Benutzer wurde in der lokalen Datenbank nicht gefunden.',111);
					return false;
				}
			} else {
				throw new Exception('Benutzer-Art wurde falsch übergeben.',113);
				return false;
			}
		} else {
			throw new Exception('Benutzername oder -Art wurde nicht übergeben',110);
			return false;
		}
	}
}
?>