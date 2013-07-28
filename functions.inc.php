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
function datum($datum) {
	$datearr = explode('.',$datum);
	if(!is_numeric($datearr[0]) || !is_numeric($datearr[1]) || !is_numeric($datearr[2])) {
		return false;
	}
	$d = array(
		$datearr[0],
		$datearr[1],
		$datearr[2],
	);
	if(checkdate($d[1],$d[0],$d[2])) {
		return $d[2].'-'.$d[1].'-'.$d[0];
	} else {
		return false;
	}
}
function checkemail($mail) 
{
	preg_match_all("/@/", $mail, $treffer);
	if(sizeof($treffer[0]) != 1) 
	{
		return false;
	}
	else
	{
		return $mail;
	}
}
?>