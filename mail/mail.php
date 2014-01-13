# !/usr/bin/php -f
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
include("/var/www/eosys/config.php");
include($conf['documentroot']."vlib/vlibTemplate.php");
include($conf['documentroot']."mysql.class.php");
include($conf['documentroot']."functions.inc.php");
include($conf['documentroot']."mail/class.phpmailer.php");

function mailtouser($mailbody,$receiveremail)
{
    $mail = new PHPMailer();
    $mail->IsSMTP();
	$mail->CharSet = 'utf-8';
    $mail->Host = ""; // Hier den SMTP-Server der E-Mail Adresse angeben
    $mail->SMTPAuth = true;
    $mail->Username = ""; // Hier E-Mail Adresse ändern
    $mail->Password = ""; // Hier Passwort ändern
    $mail->From = ""; // Hier E-Mail Adresse ändern
    $mail->FromName = "EOSys"; // Beliebiger Name
    $mail->AddAddress($receiveremail);

    $mail->Subject  =   "EOSys am XXX-Gymnasium"; // Betreff
    $mail->Body     =   $mailbody;
   
    $retcode=false;
    if(!$mail->Send())
    {
       echo $mail->ErrorInfo;
       $retcode=$mail->ErrorInfo;
    }
    else
    {
       $retcode=true;
    }
    return $retcode;
}

class fehlzeittext
{
	public $text;
	public $ok;
	
	function fehlzeittext($fid, $ffehldatum, $schuelername, $schuelercount, $kurse, $fsid, $fsname, $typ)
	{
		global $conf;
		$this->ok = false;
		if($typ == 1) 
		{
			if($fsid == 9)
			{
				$kurse .= " Stunde";
			}
			if($schuelercount > 1)
			{
				$this->text .= $schuelername." sind am ";
			}
			else
			{
				$this->text = "Der Schüler / die Schülerin ".$schuelername." ist am ";
			}
			$this->text .= $ffehldatum." für die ".$kurse." ";
			if($fsid == 9) // Durch Fachlehrer entschuldigt
			{
				$this->text .= "durch einen Fachlehrer entschuldigt.";
			}
			elseif($fsid == 3)
			{
				$this->text .= "entschuldigt.";
			}
			elseif($fsid == 6)
			{
				$this->text .= "beurlaubt.";
			}
			elseif($fsid == 8)
			{
				$this->text .= "mit Attest entschuldigt.";
			}
			else
			{
				die($fsid);
			}
			$this->text .= "\r\nDie Entschuldigung kann hier eingesehen werden: ";
			$this->text .= $conf['domainroot']."index.php?p=fz&v=".$fid."\r\n";
			
			$this->ok = true;
			return true;
		}
		elseif($typ == 2) 
		{
			$this->text = "Der Schüler / die Schülerin ".$schuelername." hat sich für den ".$ffehldatum." für den/die Kurs(e) ".$kurse." als fehlend eingetragen."."\r\n";
			$this->text .= "Als Tutor können Sie die Entschuldigung hier aufrufen und bestätigen: ";
			$this->text .= $conf['domainroot']."index.php?p=fz&v=".$fid."\r\n";
			
			$this->ok = true;
			return true;
		}
		else
		{
			return false;
		}
	}
}

class email
{
	public $ok;
	public $empfaenger;
	public $text;	
	
	function email($empfaenger,$text) 
	{
		$this->ok = false;
		$this->text = $text;
		
		$email = checkemail($empfaenger);
		if(!$email)
		{
			return false;
		}
		else
		{
			$this->empfaenger = $empfaenger;
		}
		$this->ok = true;
	}
}

$kurslehrerarray = array();
$kurslehreremailaddress = array();
$kurslehrersql = new sql();
$kurslehrerstatement = "
	Select
	  fehlzeit.fid,
	  fehlzeit.fsid,
	  Date_Format(fehlzeit.ffehldatum, '%d.%m.%Y') As ffehldatum,
	  If(Count(Distinct schueler.sname) > 1, Concat(Count(Distinct schueler.sname), ' Schüler/innen'), Concat(schueler.sname, ', ', schueler.svorname)) As schuelername,
	  Count(Distinct schueler.sname) As schuelercount,
	  Convert(Group_Concat(Distinct fehlzeitenkurse.stunde, '.', If(kurs1.kid != 1, Concat('Stunde(',kurstypen.ktname,' (', kurstypen.ktkuerzel, kurs1.knummer, '))'), '') Order By fehlzeitenkurse.stunde Separator ', ') Using  latin1) As kurse,
	  fehlzeitstatus.fsname,
	  lehrer.lid,
	  lehrer.lemail
	From
	  kurs Inner Join
	  besucht On kurs.kid = besucht.kid Inner Join
	  schueler On besucht.sid = schueler.sid Inner Join
	  fehlzeitenschueler On schueler.sid = fehlzeitenschueler.sid Inner Join
	  fehlzeit On fehlzeitenschueler.fid = fehlzeit.fid Inner Join
	  fehlzeitenkurse On fehlzeit.fid = fehlzeitenkurse.fid Inner Join
	  kurs kurs1 On fehlzeitenkurse.kid = kurs1.kid Inner Join
	  kurstypen On kurs1.ktid = kurstypen.ktid Inner Join
	  fehlzeitstatus On fehlzeit.fsid = fehlzeitstatus.fsid Inner Join
	  lehrer On lehrer.lid = kurs.lid
	Where
	  (kurs1.lid = kurs.lid Or kurs1.lid = '1') And
	  lehrer.lsendemail = 1 And
	  fehlzeitstatus.fstyp = 3 And
	  fehlzeit.faktualisiertdatum > Date_Sub(Now(), Interval 1 Day) And
	  fehlzeit.fversion = (Select
		Max(fehlzeitversiontabelle.fversion)
	  From
		fehlzeit As fehlzeitversiontabelle
	  Where
		fehlzeitversiontabelle.fid = fehlzeit.fid)
	Group By
	  fehlzeit.fid, lehrer.lid
	Order By
	  fehlzeit.ffehldatum Desc,
	  fehlzeit.fid Desc
";
$kurslehrersql->query($kurslehrerstatement);
while($kurslehrersql->fetch_obj()) 
{
	if(is_numeric($kurslehrersql->result->lid))
	{
		$kurslehrerarray[$kurslehrersql->result->lid][] = new fehlzeittext(
			$kurslehrersql->result->fid,
			$kurslehrersql->result->ffehldatum,
			$kurslehrersql->result->schuelername,
			$kurslehrersql->result->schuelercount,
			$kurslehrersql->result->kurse,
			$kurslehrersql->result->fsid,
			$kurslehrersql->result->fsname,
			1
		);
		
		if(!isset($kurslehreremailaddress[$kurslehrersql->result->lid]))
		{
			$kurslehreremailaddress[$kurslehrersql->result->lid] = $kurslehrersql->result->lemail;
		}
	}
}

foreach($kurslehrerarray as $lehrerid => $fehlzeitenarray)
{
	$emailtext = "";
	foreach($fehlzeitenarray as $fehlzeittext)
	{
		$emailtext .= $fehlzeittext->text."\r\n\r\n";
	}
	$email = new email($kurslehreremailaddress[$lehrerid], $emailtext);
	$absenden = mailtouser($email->text,$email->empfaenger);
	if($absenden) 
	{
		echo "gesendet:"."<br><br>\n\n";
		var_dump($email);
		echo "<br><br>\n\n";
	} else 
	{
		echo "nicht gesendet:"."<br><br>\n\n";
		var_dump($email);
		echo "<br><br>\n\n";
	}
}

$tutorarray = array();
$tutoremailaddress = array();
$tutorsql = new sql();
$tutorstatement = "
	Select
	  fehlzeit.fid,
	  Date_Format(fehlzeit.ffehldatum, '%d.%m.%Y') As ffehldatum,
	  fehlzeitstatus.fsname,
	  Convert(Group_Concat(Distinct fehlzeitenkurse.stunde, '.Stunde:',
	  kurstypen.ktname, ' (', kurstypen.ktkuerzel, kurs.knummer, ')' Order
	  By fehlzeitenkurse.stunde Separator ', ') Using latin1) As kurse,
	  Concat(schueler.sname, ', ', schueler.svorname) As schuelername,
	  lehrer.lid,
	  lehrer.lemail
	From
	  fehlzeit Inner Join
	  fehlzeitstatus On fehlzeitstatus.fsid = fehlzeit.fsid Inner Join
	  fehlzeitenkurse On fehlzeit.fid = fehlzeitenkurse.fid Inner Join
	  kurs On fehlzeitenkurse.kid = kurs.kid Inner Join
	  kurstypen On kurstypen.ktid = kurs.ktid Inner Join
	  abijahrgang On abijahrgang.aid = kurs.aid Inner Join
	  fehlzeitenschueler On fehlzeitenschueler.fid = fehlzeit.fid Inner Join
	  schueler On schueler.sid = fehlzeitenschueler.sid Inner Join
	  lehrer On lehrer.lid = schueler.lid
	Where
	  fehlzeitstatus.fstyp = 1 And
	  lehrer.lsendemailtutor = 1 And
	  fehlzeit.faktualisiertdatum > Date_Sub(Now(), Interval 1 Day) And
	  fehlzeit.fversion = (Select
		Max(fehlzeitversiontabelle.fversion)
	  From
		fehlzeit As fehlzeitversiontabelle
	  Where
		fehlzeitversiontabelle.fid = fehlzeit.fid)
	Group By
	  fehlzeit.fid, lehrer.lid
";
$tutorsql->query($tutorstatement);
while($tutorsql->fetch_obj()) 
{
	if(is_numeric($tutorsql->result->lid))
	{
		$tutorarray[$tutorsql->result->lid][] = new fehlzeittext(
			$tutorsql->result->fid,
			$tutorsql->result->ffehldatum,
			$tutorsql->result->schuelername,
			$tutorsql->result->schuelercount,
			$tutorsql->result->kurse,
			$tutorsql->result->fsid,
			$tutorsql->result->fsname,
			2
		);
		
		if(!isset($tutoremailaddress[$tutorsql->result->lid]))
		{
			$tutoremailaddress[$tutorsql->result->lid] = $tutorsql->result->lemail;
		}
	}
}
foreach($tutorarray as $lehrerid => $fehlzeitenarray)
{
	$emailtext = "";
	foreach($fehlzeitenarray as $fehlzeittext)
	{
		$emailtext .= $fehlzeittext->text."\r\n\r\n";
	}
	$email = new email($tutoremailaddress[$lehrerid], $emailtext);
	$absenden = mailtouser($email->text,$email->empfaenger);
	if($absenden) 
	{
		echo "gesendet:"."<br><br>\n\n";
		var_dump($email);
		echo "<br><br>\n\n";
	} else 
	{
		echo "nicht gesendet:"."<br><br>\n\n";
		var_dump($email);
		echo "<br><br>\n\n";
	}
}
?>