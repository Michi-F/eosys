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
<?php
// Diese Datei sollte täglich per cron aufgerufen werden
// Diese Datei leert die Tabelle "logins"

include('../vlib/vlibTemplate.php');
include("../config.php");
include("../mysql.class.php");


$delloginsql = new sql();
$delloginstatement = "
	Delete FROM `logins` WHERE `zeit` < date_sub(curdate(), interval 3 HOUR)
";
$delloginsql->query($delloginstatement);
?>