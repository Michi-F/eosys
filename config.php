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
$conf = array();

$salt = "SxF!VDdu.EYMgn,NpTHW"; // Hier eine eigene, zufällige Zeichenkette einfügen

//Datenbankverbindung des Entschuldigungssystems
$conf['dbhost'] = 'localhost';
$conf['dbuser'] = 'eosys';
$conf['dbpass'] = '';
$conf['db'] = 'eosys';


$conf['skursloeschen'] = false; // dürfen Schüler neue Kurse selbst hinzufügen ? true=ja, false=nein.  Standard: false
$conf['skurshinzufuegen'] = false; // dürfen Schüler falsche Kurse selbst löschen ? true=ja, false=nein.  Standard: false

$conf['domainroot'] = "https://domain.tld/eosys/";
$conf['documentroot'] = "/var/www/eosys/";
?>