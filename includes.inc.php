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
session_start();
// Template-Parser
require_once './vlib/vlibTemplate.php';

// Verbindung zur Mysql-Datenbank
include("config.php");
include("mysql.class.php");
include("remote.auth.php");
include("user.class.php");
include("logon.class.php");
include("rechte.class.php");
include("functions.inc.php");

$logon = new Auth();
if(!$logon->ok && $_GET['p']!='lo') {
	header('Location: index.php?p=lo');
	die();
} elseif($logon->ok) {
	$rechte = new rechte();
}

header('Content-Type: text/html; charset=utf-8');
?>