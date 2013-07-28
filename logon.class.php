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
class Auth extends Exception
{
	var $ok;
	public $user;

	function Auth()
	{
		$this->ok = false;
		$this->check_session();
		return $this->ok;
	}
	 
	function check_session()
	{
		if(!empty($_SESSION['a0']) && !empty($_SESSION['a1']))
		return $this->check($_SESSION['a0'], $_SESSION['a1']);
		else
		return false;
	}
	 
	function login($username, $password)
	{
		global $salt;
		try {
			$remoteuser = remoteauth($username,$password);	// LDAP-Anmeldung, Rückgabewert: Array(name,passwort,usertyp)
		} catch (Exception $e) {
			throw new Exception($e->getMessage(),$e->getCode()); // Falls LDAP fehlschlägt -> Fehler weitergeben, return false;
			return false;
		}
		if(($remoteuser[0] == '0' || $remoteuser[0] == '1' )) { // wenn schüler oder lehrer
			
			try {	// Überprüfen, ob User in lokaler DB existiert
				$user = new user($username,$remoteuser[0]);	// class user(username,usertyp)
				$this->user = $user;
				if($user->ok) {
					$_SESSION['a0'] = $user->userimportedid;
					$uniqid = md5(uniqid(mt_rand(), true).$salt);
					$_SESSION['a1'] =$uniqid;
					$loginsql = new sql();
					$loginsstatement = "
						INSERT INTO `logins` (
						`logid` ,
						`userid` ,
						`usertype` ,
						`random` ,
						`zeit`
						)
						VALUES (
						NULL , 
						'".$this->user->userimportedid."' ,
						'".$this->user->usertype."' ,
						'".$uniqid."' ,
						CURRENT_TIMESTAMP
						);
					";
					$loginsql->query($loginsstatement);
					$this->ok = true;
					return true;
				} else {
					throw new Exception('Benutzer-Login-ID konnte nicht erstellt werden.',106);
					return false;
				}
			} catch (Exception $e) {
				throw new Exception($e->getMessage(),$e->getCode());
				return false;
			}
		} else {
			throw new Exception('Remote-Login: User-Typ wurde falsch übergeben.',107);
			return false;
		}
	}
	
	function check($username,$loginid)
	{
		global $salt;
		try {	// Überprüfen, ob User in lokaler DB existiert
			$loginsql = new sql();
			$loginsqlstatement = "
				SELECT *  FROM `logins` WHERE `userid` = '".mysql_real_escape_string($username)."' AND `zeit` > date_sub(curdate(), interval 3 HOUR) ORDER BY logid DESC Limit 0,1
			";
			$loginsql->query($loginsqlstatement);
			if($loginsql->num_rows() != 1) {
				$this->logout();
				return false;
			}
			$loginsql->fetch_obj();
			if(($loginsql->result->random == $loginid)) {
				if($loginsql->result->usertype == 0 || $loginsql->result->usertype == 1 ) { // wenn schüler oder lehrer
					try {	// Überprüfen, ob User in lokaler DB existiert
						$user = new user($loginsql->result->userid,$loginsql->result->usertype);	// class user(username,usertyp)
						$this->user = $user;
						if($user->ok) {
							$this->ok = true;
							return true;
						} else {
							$this->logout();
							return false;
						}
					} catch (Exception $e) {
						$this->logout();
						return false;
					}
				} else {
					$this->logout();
					return false;
				}
			} else {
				$this->logout();
				return false;
			}
		} catch (Exception $e) {
			$this->logout();
			return false;
		}
	}
	function logout()
	{
		$this->ok = false;
		 
		$_SESSION['a0'] = "";
		$_SESSION['a1'] = "";
	}
}
?>