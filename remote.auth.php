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
function remoteauth($username,$password) 
{
	// An diese Funktion wird $username und $password (Klartext, wie vom Benutzer eingegeben) übergeben.
	// Diese Funktion muss ein Array mit 1 Element zurückgeben.
	// wenn $returnarray[0] = 1 ist, wird der Benutzer als Lehrer authentifiziert
	// wenn $returnarray[0] = 0 ist, wird der Benutzer als Schüler authentifiziert
	// wenn die Rückgabe false ist, wird der Login abgebrochen.

	// Eine einfache Demo.
	if($password == "l") // l für lehrer
	{
		$returnarray[0] = 1;
	}
	elseif($password == "s") // s für schüler
	{
		$returnarray[0] = 0;
	}
	else
	{
		throw new Exception("Benutzername / Kennwort falsch !",100);
		return false;
	}
	return $returnarray;

	// LDAP-Beispiel
	/*
	$ldap = ldap_connect("localhost",389);
	$bindconnectiontest = ldap_bind($ldap,"","");
	if(!$bindconnectiontest) {
		throw new Exception("LDAP: Keine Verbindung zum LDAP-Server !",101);
		return false;
	}
	@$bind = ldap_bind($ldap,"uid=".$username.",ou=accounts,dc=schule,dc=local",$password);
	if($bind){
		$search=ldap_search($ldap,"ou=accounts,dc=schule,dc=local", "uid=".$username);
		$daten = ldap_get_entries($ldap, $search);
		// Prüfen, ob Objekt in der Lehrer-Gruppe ist
		if($daten[0]['gidnumber'][0] == 'id_der_lehrergruppe') {
			$returnarray[0] = 1;
		} else {
			$returnarray[0] = 0;
		}
		return $returnarray;
	} else {
		throw new Exception("LDAP: Benutzername / Kennwort falsch !",100);
		return false;
	}
	ldap_close($ldap);
	*/
}
?>