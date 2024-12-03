<?php
namespace tgdb;
require_once("include/include.php");
use DateTime;
navbar::setactive("player");

$user = auth();

$searchquery = false;
if (isset($_GET['playerckey']) || isset($_GET['playercid']) || isset($_GET['playerip']) || isset($_GET['adminckey'])) {
	$searchquery = true;
}

if (!isset($_GET['ckey'])) {
	if (isset($_GET['playerckey']) || isset($_GET['playercid']) || isset($_GET['playerip']))
		header("location: conndb.php?".$_SERVER["QUERY_STRING"]);
	else if (isset($_GET['adminckey']))
		header("location: bans.php?".$_SERVER["QUERY_STRING"]);
	else
		header('location: player.php');
	die();
}
$thm = new theme("Player Details");
$tpl = new template("playerdetails", array(
	"USERCKEY"	 			=>	crossrefify($user[0], 'adminckey'), 
	"USERRANK" 				=>	$user[1],
	"PLAYERCKEY"			=>	crossrefify(htmlspecialchars($_GET['ckey']), 'ckey')
	));

$row = null;
$sqlwhere = " WHERE ckey = '".esc(keytockey($_GET['ckey'],FALSE))."'";
$ckey = keytockey($_GET['ckey'],FALSE);
$res = $mysqli->query("SELECT firstseen, lastseen, ip, computerid, (SELECT rank FROM `".fmttable("admin")."`".$sqlwhere.") AS lastadminrank, (SELECT count(ckey) FROM `".fmttable("connection_log")."`".$sqlwhere.") AS connection_count FROM `".fmttable("player")."`".$sqlwhere);
if (!($row = $res->fetch_assoc())) {
	$tpl->setvar("ERROR_MSG", "Nobody found for ckey ".$_GET['ckey']);
	$thm->send($tpl);
	exit;
}

$tpl->setvar("FIRSTSEEN",$row['firstseen']);
$tpl->setvar("LASTSEEN",$row['lastseen']);
$tpl->setvar("LASTCOMPUTERID",crossrefify($row['computerid'],'cid'));
$tpl->setvar("LASTIP",crossrefify($row['ip'],'ip'));
$tpl->setvar("LASTADMINRANK",$row['lastadminrank']);
$tpl->setvar("ROUNDCOUNT",$row['connection_count']);

//now lets unset and free all the things
unset($row);
$res->free();

//now we build the other tables. 
$cids = array();
$res = $mysqli->query("SELECT computerid, count(computerid) AS count FROM `".fmttable("connection_log")."`".$sqlwhere." GROUP BY computerid ORDER BY count DESC");
while ($row = $res->fetch_assoc()) {
	$cids[] = array('CID' => crossrefify($row['computerid'],cid), 'ROUNDS' => $row['count']);
}

$tpl->setvar('CIDS', $cids);
$tpl->setvar('CIDCOUNT', count($cids));
$tpl->setvar('CIDTABLEOPEN', (!count($cids) || count($cids) > 5 ? "collapse" : "in"));
$res->free();

$ips = array();
$res = $mysqli->query("SELECT ip, count(ip) AS count FROM `".fmttable("connection_log")."`".$sqlwhere." GROUP BY ip ORDER BY count DESC");
while ($row = $res->fetch_assoc()) {
	$ips[] = array('IP' => crossrefify($row['ip'],ip), 'ROUNDS' => $row['count']);
}

$tpl->setvar('IPS', $ips);
$tpl->setvar('IPCOUNT', count($ips));
$tpl->setvar('IPTABLEOPEN', (!count($ips) || count($ips) > 5 ? "collapse" : "in"));

$res->free();

$res = $mysqli->query("SELECT id, bantime, bantype, reason, job, a_ckey, expiration_time, unbanned, unbanned_datetime, unbanned_ckey FROM ".fmttable("ban").$sqlwhere." ORDER BY bantime DESC;");

$permabanned = FALSE;
$banned = FALSE;
$jobbanned = FALSE;
$idbanned = FALSE;
$adminbanned = FALSE;
$activebans = 0;
$realbans = 0;
$lastbanreason = "";
$lastbantype = "";
$bans = array();
while ($row = $res->fetch_assoc()) {
	$ban = array();
	$active = FALSE;
	$bantime = new DateTime($row['bantime']);
	$banexpires = new DateTime($row['expiration_time']);
	
	$banlen = generateDurationFromDates($bantime, $banexpires);
	
	$ban['BAN_STATUS'] = ($row['unbanned'] ? 'Unbanned':'Active');
	
	//Stuff we only do when the ban isn't a permaban
	if (strpos($row['bantype'],"PERMA") === FALSE) {
		if (!$row['unbanned'] && $banexpires < (New DateTime()))
			$ban['BAN_STATUS'] = 'Expired';
		else
			$active = TRUE;
		$ban['EXPIRE_TIME'] = $row['expiration_time'];
		$ban['BAN_LENGTH'] = $banlen." Minute".($banlen==1?"":"s");
	} else {
		$active = TRUE;
	}
	
	if ($row['unbanned']) {
		$active = false;
		$ban['UNBANNED'] = $row['unbanned'];		
		$ban['UNBANNING_ADMIN'] = crossrefify($row['unbanned_ckey'], "adminckey");
		$ban['UNBAN_TIME'] = $row['unbanned_datetime'];
	}

	if ($row['job'])
		$ban['BAN_JOB'] = $row['job'];
		
	$ban['BAN_REASON'] = htmlspecialchars($row['reason']);
	
	
	
	$ban['BANNING_ADMIN'] = crossrefify($row['a_ckey'],"adminckey");
	
	$ban['BAN_DATE'] = $row['bantime'];
	
	$ban['BAN_ID'] = $row['id'];
	if ($active) {
		if (strpos($row['bantype'], "JOB_") !== FALSE)
			$jobbanned = TRUE;
		else if (strpos($row['bantype'], "APPEARANCE_") !== FALSE)
			$idbanned = TRUE;
		else if (strpos($row['bantype'], "ADMIN_") !== FALSE)
			$adminbanned = TRUE;
		else if ($row['bantype'] == "PERMABAN")
			$permabanned = TRUE;
		else
			$banned = TRUE;
		if ($lastbanreason !== $row['reason'] || $lastbantype !== $row['bantype'])
			$activebans++;
	}
	if ($lastbanreason != $row['reason'] || $lastbantype != $row['bantype'])
		$realbans++;
	$lastbanreason = $row['reason'];
	$lastbantype = $row['bantype'];
	$bans[] = $ban;
	
}
$tpl->setvar('CKEY', $ckey);
$tpl->setvar('BANS', $bans);
$tpl->setvar('BANCOUNT', count($bans));
$tpl->setvar('BANTABLEOPEN', (!count($bans) || count($bans) > 5 ? "collapse" : "in"));
$tpl->setvar('BANNED', $banned);
$tpl->setvar('JOBBANNED', $jobbanned);
$tpl->setvar('ADMINBANNED', $adminbanned);
$tpl->setvar('IDBANNED', $idbanned);
$tpl->setvar('PERMABANNED', $permabanned);
if (!$banned && !$permabanned && !$idbanned && !$adminbanned && !$jobbanned)
	$tpl->setvar('CLEANSTANDING', TRUE);
$tpl->setvar('ACTIVEBANS', $activebans);
$tpl->setvar('REALBANS', $realbans);

$res->free();

$res = $mysqli->query("SELECT timestamp, notetext, adminckey, server FROM ".fmttable("notes").$sqlwhere." ORDER BY timestamp DESC;");
$notes = array();
while ($row = $res->fetch_assoc()) {
	$note = array();
	$note['DATE'] = $row['timestamp'];
	$note['ADMIN'] = $row['adminckey'];
	$note['SERVER'] = $row['server'];
	$note['NOTE'] = $row['notetext'];
	$notes[] = $note;
}
$tpl->setvar('NOTES', $notes);
$tpl->setvar('NOTECOUNT', count($notes));
$tpl->setvar('NOTETABLEOPEN', (!count($notes) || count($notes) > 5 ? "collapse" : "in"));

$res->free();

$sqlwhere = " WHERE targetckey = '".esc(keytockey($_GET['ckey'],FALSE))."'";
$res = $mysqli->query("SELECT timestamp, text, adminckey, type, server FROM ".fmttable("messages").$sqlwhere." AND (type = 'message' OR type = 'message sent') ORDER BY timestamp DESC;");
$messages = array();
while ($row = $res->fetch_assoc()) {
	$message = array();
	$message['DATE'] = $row['timestamp'];
	$message['ADMIN'] = $row['adminckey'];
	$message['SERVER'] = $row['server'];
	if ($row['type'] == "message sent") {
		$message['READ'] = $row['type'];
	}
	$message['MESSAGE'] = $row['text'];
	$messages[] = $message;
}
$tpl->setvar('MESSAGES', $messages);
$tpl->setvar('MESSAGECOUNT', count($messages));
$tpl->setvar('MESSAGETABLEOPEN', (!count($messages) || count($messages) > 5 ? "collapse" : "in"));

$res->free();

$thm->send($tpl);

?>
