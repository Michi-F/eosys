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
$menue = new vlibTemplate('menue.html');
if($logon->ok) {
	$menue->setvar("logon","1");
} else {
	$menue->setvar("logon","0");
}
if($logon->user->usertype == 1) {
	$menue->setvar("usertype","1");
} elseif($logon->user->usertype == 0) {
	$menue->setvar("usertype","0");
} else {
	$logon->logout();
}
if($rechte->adminfunktionen)
{
	$menue->setvar("adminfunktionen","1");
}
else 
{
	$menue->setvar("adminfunktionen","0");
}
$menue->pparse();
?>