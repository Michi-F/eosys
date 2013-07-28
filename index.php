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
//error_reporting(E_ALL ^E_DEPRECATED ^E_USER_DEPRECATED  ^ E_NOTICE);
//ini_set('display_errors', 1);
ob_start();
include("includes.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>EOSys - Online System an Schulen</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<link rel="stylesheet" type="text/css" href="account.css">
		<link rel="stylesheet" type="text/css" href="newfzkurse.css">
		<link rel="stylesheet" type="text/css" href="newfz.css">
		<link rel="stylesheet" type="text/css" href="uebersichtfz.css">
		<link rel="stylesheet" type="text/css" href="printfz.css" media="print" />
		
		<!-- Kalender: -->
		<link rel="stylesheet" type="text/css" href="kalender/skins/aqua/theme.css" />
		<script type="text/javascript" src="kalender/calendar.js"></script>
		<script type="text/javascript" src="kalender/calendar-setup.js"></script>
		<script type="text/javascript" src="kalender/lang/calendar-de.js"></script>
		<!-- ende Kalender -->
		
	</head>
	<body>
		<div id="kopf"> 
			<img id="kopf" src="headerschrift.gif">
		</div>
		<div id="trennzeile">&nbsp;</div>
		<div id="menuecolor">&nbsp;</div>
		<div id="menu"><?php include("menue.php"); ?></div>
		<div id="inhalt">
		<?php
			switch($_GET['p']) {
			case "fz":		// neue Fehlzeit schreiben
				include('newfz.php');
			break;
			case "ue":		// Übersicht aller Fehlzeiten
				include('uebersichtfz.php');
			break;
			case "acc":		// Account-Verwaltung
				include('account.php');
			break;
			case "lo":		// Logon
				include('logon.php');
			break;
			case "ku":		// Kurse verwalten
				include('kurse.php');
			break;
			case "ki":		// Kursinformationen anzeigen
				include('kurseinfo.php');
			break;
			case "leh":		// Accounts verwalten
				include('lehrer.php');
			break;
			case "ent":		// 
				include('tutorentsch.php');
			break;
			case "del":		// 
				include('delete.php');
				break;
			case "deljg":		// Jahrgang löschen
				include('deletejg.php');
				break;
			case "ausw": // Fehlzeiten Auswertung
				include("fzauswertung.php");
			break;
			default:	// neue FZ erstellen
				include('newfz.php');
			break;
		}
		?>
		</div>
	</body>
</html>
<?php
ob_end_flush();
?>