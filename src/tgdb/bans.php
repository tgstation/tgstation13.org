<?php
namespace tgdb;
use DateTime;
require_once("include/include.php");
navbar::setactive("ban");

$user = auth();

$tpl = new template("bans", array(
	"USERCKEY"	 	=>	crossrefify($user[0],'adminckey'), 
	"USERRANK" 		=>	$user[1],
	"ADMINCKEY" 	=>	"",
	"PLAYERCKEY" 	=>	"",
	"PLAYERCID" 	=>	"",
	"PLAYERIP" 		=>	"",
	"PANELOPEN"		=>	"collapse"
	));
	
$thm = new theme("Bans");

$sqlwherea = array();
if (isset($_GET['adminckey']) && $_GET['adminckey']) {
	$_GET['adminckey'] = keytockey($_GET['adminckey']);
	$adminckey = "'".esc($_GET['adminckey'])."'";
	$sqlwherea[] = "a_ckey LIKE ".$adminckey;
	$tpl->setvar('ADMINCKEY', htmlspecialchars($_GET['adminckey']));
}

//player search stuff, uses its own array because it needs to OR its search in () then AND it with the admin search.
$playersearcha = array();
if (isset($_GET['playerckey']) && $_GET['playerckey']) {
	$_GET['playerckey'] = keytockey($_GET['playerckey']);
	$playerckey = "'".esc($_GET['playerckey'])."'";
	$playersearcha[] = "ckey LIKE ".$playerckey;
	$tpl->setvar('PLAYERCKEY', htmlspecialchars($_GET['playerckey']));
}

if (isset($_GET['playercid']) && $_GET['playercid']) {
	$playercid = "'".esc($_GET['playercid'])."'";
	$playersearcha[] = "computerid LIKE ".$playercid;
	$tpl->setvar('PLAYERCID', htmlspecialchars($_GET['playercid']));
}

if (isset($_GET['playerip']) && $_GET['playerip']) {
	$playerip = "'".esc($_GET['playerip'])."'";
	$playersearcha[] = "ip LIKE ".$playerip;
	$tpl->setvar('PLAYERIP', htmlspecialchars($_GET['playerip']));
}

if (count($playersearcha)) {
	$sqlwherea[] = "(". join(" OR ", $playersearcha) .")";
}

$sqlwhere = "";
if (count($sqlwherea)) {
	//show the search panel
	$tpl->setvar('PANELOPEN', 'in');
	$sqlwhere = " WHERE ".join(" AND ", $sqlwherea);
}
$limit = 200;
if (isset($_GET['limit']))
	$limit = 0+$_GET['limit']+0;
$res = $mysqli->query("SELECT id, bantime, ckey, computerid, ip, bantype, reason, job, a_ckey, expiration_time, unbanned, unbanned_datetime, unbanned_ckey FROM ".fmttable("ban").$sqlwhere." ORDER BY id DESC LIMIT ".$limit.";");

$banrows = array();
while ($row = $res->fetch_assoc()) {
	$bantime = new DateTime($row['bantime']);
	$banexpires = new DateTime($row['expiration_time']);
	
	$banlen = generateDurationFromDates($bantime, $banexpires);
	$banrow = array();
	$banrow['BAN_STATUS'] = ($row['unbanned'] ? 'Unbanned':'Active');
	
	//Stuff we only do when the ban isn't a permaban
	if (strpos($row['bantype'],"PERMA") === FALSE) {
		if (!$row['unbanned'] && $banexpires < (New DateTime()))
			$banrow['BAN_STATUS'] = 'Expired';
			
		$banrow['EXPIRE_TIME'] = $row['expiration_time'];
		$banrow['BAN_LENGTH'] = $banlen." Minute".($banlen==1?"":"s");
	}
	
	if ($row['unbanned']) {
		$banrow['UNBANNED'] = $row['unbanned'];		
		$banrow['UNBANNING_ADMIN'] = crossrefify($row['unbanned_ckey'], "adminckey");
		$banrow['UNBAN_TIME'] = $row['unbanned_datetime'];
	}
	
	if ($row['ckey'])
		$banrow['BANNED_CKEY'] = crossrefify($row['ckey'],'ckey');
	if ($row['computerid'])
		$banrow['BANNED_CID'] = crossrefify($row['computerid'],'cid');
	if ($row['ip'])
		$banrow['BANNED_IP'] = crossrefify($row['ip'],'ip');

	if ($row['job'])
		$banrow['BAN_JOB'] = $row['job'];
		
	$banrow['BAN_REASON'] = htmlspecialchars($row['reason']);
	
	
	
	$banrow['BANNING_ADMIN'] = crossrefify($row['a_ckey'],"adminckey");
	
	$banrow['BAN_DATE'] = $row['bantime'];
	
	$banrow['BAN_ID'] = $row['id'];
	
	$banrows[] = $banrow;
}

$tpl->setvar("BAN_ROWS", $banrows);
$tpl->setvar('BANTABLE', $bantabletpl);
$thm->send($tpl);
?>