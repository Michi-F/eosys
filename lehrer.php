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
if(isset($_POST['lname']) && !empty($_POST['lvorname']) && !empty($_POST['lkuerzel']) && !empty($_POST['limportedid']) )
{
	$insertlehrer = new sql();
	$insertlehrerstatement = "
		INSERT INTO `lehrer` (`lid`, `limportedid`, `lkuerzel`, `lname`, `lvorname`, `lemail`, `lsendemail`, `lsendemailtutor`) VALUES 
		(NULL, '".mysql_real_escape_string($_POST['limportedid'])."', '".mysql_real_escape_string($_POST['lkuerzel'])."', 
		'".mysql_real_escape_string($_POST['lname'])."', '".mysql_real_escape_string($_POST['lvorname'])."', 
		'".mysql_real_escape_string($_POST['lemail'])."', 1, 1);
	";
	$insertlehrer->query($insertlehrerstatement);
}
$sql = new sql();
$lehrer = new vlibTemplate('lehrer.html');

$lehrerabfragestatement = '
	Select
	  lehrer.lid,
	  lehrer.limportedid,
	  lehrer.lname,
	  lehrer.lvorname,
	  lehrer.lkuerzel,
	  lehrer.lemail
	From
	  lehrer
	Order By
	  lehrer.lid ASC
';
$sql->query($lehrerabfragestatement);
$lehrer->setdbloop('lehrer', $sql->query);
$lehrer->pparse();
?>