<?php
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

$res = $mysqli->query("SELECT id, bantime, ckey, computerid, ip, bantype, reason, job, a_ckey, expiration_time, unbanned, unbanned_datetime, unbanned_ckey FROM ".fmttable("ban").$sqlwhere." ORDER BY id DESC LIMIT 250;");

$banrowtpl = new template("banrow");
$banrows = "";
while ($row = $res->fetch_assoc()) {
	$bantime = new DateTime($row['bantime']);
	$banexpires = new DateTime($row['expiration_time']);
	
	$banlen = generateDurationFromDates($bantime, $banexpires);
	
	$banrowtpl->setvar('BAN_STATUS', ($row['unbanned'] ? 'Unbanned':'Active'));
	
	//Stuff we only do when the ban isn't a permaban
	if (strpos($row['bantype'],"PERMA") === FALSE) {
		if (!$row['unbanned'] && $banexpires < (New DateTime()))
			$banrowtpl->setvar('BAN_STATUS', 'Expired');
			
		$banrowtpl->setvar('EXPIRE_TIME', $row['expiration_time']);
		$banrowtpl->setvar('BAN_LENGTH', $banlen." Minute".($banlen==1?"":"s"));
	}
	
	if ($row['unbanned']) {
		$banrowtpl->setvar('UNBANNED', $row['unbanned']);		
		$banrowtpl->setvar('UNBANNING_ADMIN', crossrefify($row['unbanned_ckey'], "adminckey"));
		$banrowtpl->setvar('UNBAN_TIME', $row['unbanned_datetime']);
	}
	
	if ($row['ckey'])
		$banrowtpl->setvar('BANNED_CKEY', crossrefify($row['ckey'],'ckey'));
	if ($row['computerid'])
		$banrowtpl->setvar('BANNED_CID', crossrefify($row['computerid'],'cid'));
	if ($row['ip'])
		$banrowtpl->setvar('BANNED_IP', crossrefify($row['ip'],'ip'));

	if ($row['job'])
		$banrowtpl->setvar('BAN_JOB', $row['job']);
		
	$banrowtpl->setvar('BAN_REASON', htmlspecialchars($row['reason']));
	
	
	
	$banrowtpl->setvar('BANNING_ADMIN', crossrefify($row['a_ckey'],"adminckey"));
	
	$banrowtpl->setvar('BAN_DATE', $row['bantime']);
	
	$banrowtpl->setvar('BAN_ID', $row['id']);
	
	$banrows .= $banrowtpl->process();
}

$bantabletpl = new template("bantable", array("BAN_ROWS" => $banrows));
$tpl->setvar('BANTABLE', $bantabletpl);
$thm->send($tpl);
?>