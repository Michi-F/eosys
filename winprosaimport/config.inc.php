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
// Aus diesen Feldern der CSV-Datei werden die persönlichen Daten ausgelesen:
$schuelerfelder = array(
	"abijahrgang" => "AbiJahr",
	"vorname" => "Vorname",
	"nachname" => "Name",
	"simportedid" => "LogN", # eindeutige Namens-ID (Login-Name)
	"idnr" => "IDNR", # eindeutige Identifikationsnummer (nicht unbedingt notwendig, aber zur Identifikation des Datensatzes nützlich)
	"geburtsdatum" => "GebDat",
	"tutor" => "Tutor",
	"relivalue" => "besKonf" // hier wird festgelegt, ob der Schüler in evR (evangelisch Reli), kR (katholisch Reli) oder Eth (Ethik)-Kurs ist
);

$relikonfessionen = array(
	0=>"Eth",
	1=>"evR",
	2=>"kR"
);

$vierstuendigebezeichnungen = array( # bei diesen Werten im SemX-xxx Feld wird angenommen, dass der Schueler das Fach als 4-Stündiges Fach belegt hat
	"Ks","ks","k","km","bl","Mv"
);
$zweistuendigebezeichnungen = array( # bei diesen Werten im SemX-xxx Feld wird angenommen, dass der Schueler das Fach als 2-Stündiges Fach belegt hat
	"M","x","ek","gk","wi"
);
$nichtbelegtbezeichnungen = array( # bei diesen Werten im SemX-xxx Feld wird angenommen, dass der Schueler das Fach nicht belegt hat
	".","{t}"
);
?>