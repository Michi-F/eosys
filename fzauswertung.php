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
if($logon->user->usertype != 1)
{
	header("Location: index.php");
}

$where = "";
if(isset($_GET["a"]) && $rechte->adminfunktionen === true)
{
	$where = "1=1";
}
else
{
	$where = "schueler.lid = '".$logon->user->userid."'";
}

$fzawtmpl = new vlibTemplate("fzauswertung.html");

$sort = "";
if(isset($_GET['s']) && is_numeric($_GET['s']))
{
	switch($_GET['s'])
	{
		case 1:
			$sort = "schuelername,";
			break;
		case 2:
			$sort = "bereinigteflnvcount DESC,";
			break;
		case 3:
			$sort = "flnv.flnvcount DESC,";
			break;
		case 4:
			$sort = "flv.flvcount DESC,";
			break;
		case 5:
			$sort = "fne.fnecount DESC,";
			break;
		case 6:
			$sort = "fna.fnacount DESC,";
			break;
		default:
			break;
	}
}

$fzauswertungsql = new sql();
$fzauswertungstatement = "
	Select
	  schueler.sid,
	  Concat(schueler.sname, ', ', schueler.svorname) As schuelername,
	  abijahrgang.aname,
	  (lehrer.lname) As tutor,
	  (flnv.flnvcount - If(flv.flvcount > 0, flv.flvcount,
	  0)) As bereinigteflnvcount,
	  flnv.flnvcount,
	  If(flv.flvcount > 0, flv.flvcount, 0) As flvcount,
	  fne.fnecount,
	  fna.fnacount
	From
	  schueler Inner Join
	  lehrer On schueler.lid = lehrer.lid Inner Join
	  abijahrgang On schueler.aid = abijahrgang.aid Left Join
	  (Select
		Count(fehlzeit.fid) As flnvcount,
		fehlzeitenschueler.sid
	  From
		fehlzeitenschueler Inner Join
		fehlzeit On fehlzeitenschueler.fid = fehlzeit.fid
	  Where
		fehlzeit.fsid In (4) And
		fehlzeit.fversion = (Select
		  Max(fehlzeitversiontabelle.fversion)
		From
		  fehlzeit As fehlzeitversiontabelle
		Where
		  fehlzeitversiontabelle.fid = fehlzeit.fid)
	  Group By
		fehlzeitenschueler.sid
	  Order By
		Count(fehlzeit.fid) Desc) flnv On flnv.sid = schueler.sid Left Join
	  (Select
		fehlzeitenschueler.sid,
		Count(Distinct fehlzeit.fid) As flvcount,
		Group_Concat(fehlzeit.fid, ' - ', fehlzeit1.fid Separator '\n')
	  From
		fehlzeit Inner Join
		fehlzeitenschueler On fehlzeit.fid = fehlzeitenschueler.fid Inner Join
		fehlzeitenschueler fehlzeitenschueler1 On fehlzeitenschueler.sid =
		  fehlzeitenschueler1.sid Inner Join
		fehlzeit fehlzeit1 On fehlzeitenschueler1.fid = fehlzeit1.fid And
		  fehlzeit.ffehldatum = fehlzeit1.ffehldatum
	  Where
		fehlzeit.fid != fehlzeit1.fid And
		(fehlzeit.fsid In (1, 3, 5, 6, 8, 9) And
		fehlzeit1.fsid In (4) And
		fehlzeit.fversion = (Select
		  Max(fehlzeitversiontabelle.fversion)
		From
		  fehlzeit As fehlzeitversiontabelle
		Where
		  fehlzeitversiontabelle.fid = fehlzeit.fid) And
		fehlzeit1.fversion = (Select
		  Max(fehlzeitversiontabelle2.fversion)
		From
		  fehlzeit As fehlzeitversiontabelle2
		Where
		  fehlzeitversiontabelle2.fid = fehlzeit1.fid))
	  Group By
		fehlzeitenschueler.sid
	  Order By
		Count(Distinct fehlzeit.fid) Desc) flv On flv.sid = flnv.sid Left Join
	  (Select
		fehlzeitenschueler.sid,
		Count(fehlzeit.fid) As fnecount
	  From
		fehlzeitenschueler Inner Join
		fehlzeit On fehlzeitenschueler.fid = fehlzeit.fid Inner Join
		schueler On schueler.sid = fehlzeitenschueler.sid
	  Where
		fehlzeit.fsid In (1, 5) And
		fehlzeit.fversion = (Select
		  Max(fehlzeitversiontabelle.fversion)
		From
		  fehlzeit As fehlzeitversiontabelle
		Where
		  fehlzeitversiontabelle.fid = fehlzeit.fid)
	  Group By
		fehlzeitenschueler.sid, schueler.sname
	  Order By
		Count(fehlzeit.fid) Desc) fne On fne.sid = schueler.sid Left Join
	  (Select
		fehlzeitenschueler.sid,
		Count(fehlzeit.fid) As fnacount
	  From
		fehlzeitenschueler Inner Join
		fehlzeit On fehlzeitenschueler.fid = fehlzeit.fid Inner Join
		schueler On schueler.sid = fehlzeitenschueler.sid
	  Where
		fehlzeit.fsid In (2) And
		fehlzeit.fversion = (Select
		  Max(fehlzeitversiontabelle.fversion)
		From
		  fehlzeit As fehlzeitversiontabelle
		Where
		  fehlzeitversiontabelle.fid = fehlzeit.fid)
	  Group By
		fehlzeitenschueler.sid, schueler.sname
	  Order By
		Count(fehlzeit.fid) Desc) fna On fna.sid = schueler.sid
	Where
	  ".$where."
	  /*(
	  (flnv.flnvcount > 0) Or
	  (fne.fnecount > 0) Or
	  (fna.fnacount > 0)
	  )*/
	Order By
	  ".$sort."
	  bereinigteflnvcount Desc,
	  flnv.flnvcount Desc,
	  fne.fnecount Desc,
	  schueler.sid
";
$fzauswertungsql->query($fzauswertungstatement);

$fzawtmpl->setdbloop("auswertungloop",$fzauswertungsql->query);

$fzawtmpl->pparse();
?>