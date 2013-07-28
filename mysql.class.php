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
class sql
{
	var $verbindung;
	var $query;
	var $result;

	function sql()
	{
		global $conf;
		$this->verbinde($conf['dbhost'],$conf['dbuser'],$conf['dbpass'],$conf['db']);
	}
	
	function verbinde($server,$user,$pass,$datenbank) {
		@$this->verbindung = mysql_connect($server,$user,$pass);
		if ($this->verbindung)
		{
			
			mysql_set_charset('utf8',$this->verbindung); 
			if (@!mysql_select_db($datenbank))
			{
				$this->error("Datenbank nicht gefunden: ".$datenbank, mysql_errno(), mysql_error());
			}
		}
		else 
		{
			$this->error("Keine Verbindung zum MySQL-Server",mysql_errno(), mysql_error());
		}
	}
	
	function query($abfrage)
	{
		if(@!$this->query = mysql_query($abfrage))
		{
			$this->error("Query fehlgeschlagen: <pre>".$abfrage."</pre>", mysql_errno(), mysql_error());
		}
	}

	function fetch_obj()
	{
		if (@$this->result = mysql_fetch_object($this->query))
		{
			return $this->result;
		}
	}
	
	function fetch_arr()
	{

		if (@$this->result = mysql_fetch_array($this->query, MYSQL_ASSOC))
		{
			return $this->result;
		}
	}

	function num_rows()
	{
		if (@$this->result = mysql_num_rows($this->query))
		{
			return $this->result;
		}
	}

	function error($fehlermeldung,$nummer,$error) {
		$errortmpl = new vlibTemplate("mysqlerror.html");
		$errortmpl->setvar("fehler",$error);
		$errortmpl->setvar("statement",$fehlermeldung);
		$errortmpl->setvar("nummer",$nummer);
		$errortmpl->pparse();
		die("MySQL-Error: mysql.class.php");
	}
}
?>