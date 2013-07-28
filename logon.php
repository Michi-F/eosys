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
$logontmpl = new vlibTemplate('logon.html');
$logontmpl->setvar("weiterleitung","0");

if(isset($_GET['l']) && $_GET['l']=='lo') {
	$logon->logout();
}

if($logon->ok) {
	$logontmpl->setvar("logonmsgheadline","Sie sind angemeldet.");
	$logontmpl->setvar("logonmsgaction","");
	$logontmpl->setvar("logonform","0");
	$logontmpl->pparse();
	exit();
}

if(isset($_POST['logonname'])) {
	if(isset($_POST['logonpw']) && !empty($_POST['logonname']) && !empty($_POST['logonpw'])) 
	{
		try {
			$logon->login($_POST['logonname'],$_POST['logonpw']);
		} catch (Exception $e) {
			if($e->getCode()== 100) {
				$logontmpl->setvar("logonmsgaction","Fehler:<br>Benutzername/Passwort war falsch.");
			} else {
				$logontmpl->setvar("logonmsgaction","Es ist ein Fehler aufgetreten:<br>Bitte wenden Sie sich an den Administrator ! \n <br><br> Details:<br> >".$e->getMessage()."< \n <br> Code: >".$e->getCode()."<");
			}
		}
	} else {
		$logontmpl->setvar("logonmsgaction","Sie haben kein Benutzername / Passwort angegeben.");
	}
} else {
	$logontmpl->setvar("logonmsgaction","Bitte melden Sie sich an.");
}
if($logon->ok) {
	$logontmpl->setvar("logonmsgheadline","Erfolgreicher Login.");
	$logontmpl->setvar("weiterleitung","1");
	$logontmpl->setvar("target","index.php?p=fz");
	$logontmpl->setvar("logonmsgaction","Sie werden in 1 Sekunde weitergeleitet...");
	$logontmpl->setvar("logonform","0");
} else {
	$logontmpl->setvar("logonmsgheadline","Sie sind nicht angemeldet.");
	$logontmpl->setvar("logonform","1");
}

$logontmpl->pparse();
?>