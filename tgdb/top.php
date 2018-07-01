<?php
namespace tgdb;
$totaltime = microtime(true);
require_once("include/include.php");

$user = auth(FALSE);
$thm = new theme("Top Players");
$tpl = new template("top", array(
	"USERCKEY"	 			=>	$user[0], 
	"USERRANK" 				=>	$user[1],
	"FILTERNONECHECKED"		=>	"checked",
	"FILTERPLAYERSCHECKED"	=>	"",
	"FILTERADMINCHECKED"	=>	"",
	"PANELOPEN"				=>	"collapse in"
	));

$sqlwhere = "";

if (isset($_GET['filtertype']) && $_GET['filtertype'] != "all") {
	$tpl->setvar("FILTERNONECHECKED", "");	
	$tpl->setvar('PANELOPEN', 'in');
	$sqlsub = '(SELECT ckey FROM `'.fmttable('player').'` WHERE lastadminrank != \'player\')';
	if ($_GET['filtertype'] == "players") {
		$tpl->setvar("FILTERPLAYERCHECKED", "checked");
		$sqlwhere = " WHERE ckey NOT IN ".$sqlsub;
	} else if ($_GET['filtertype'] == "admins") {
		$tpl->setvar("FILTERADMINCHECKED", "checked");
		$sqlwhere = " WHERE ckey IN ".$sqlsub;
	}
}

$res = $mysqli->query("SELECT ckey, count(*) AS `count` FROM `".fmttable("connection_log")."`".$sqlwhere." GROUP BY ckey ORDER BY count DESC LIMIT 500");

$ckeys = array();
$i = 1;
while ($res && $row = $res->fetch_row()) {
	$ckey = array();
	$ckey['RANK'] = $i++;
	$ckey['CKEY'] = $row[0];
	$ckey['CONNECTIONS'] = $row[1];
	$ckeys[] = $ckey;
}

$tpl->setvar('CKEYS', $ckeys);
$thm->send($tpl);
?>