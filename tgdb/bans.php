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

$res = $mysqli->query("SELECT ckey,computerid,ip,bantype,reason,job,duration,a_ckey,bantime,unbanned,unbanned_datetime,unbanned_ckey,id,expiration_time FROM ".fmttable("ban").$sqlwhere." ORDER BY id DESC LIMIT 250;");

$banrowtpl = new template("banrow");
$banrows = "";
while ($row = $res->fetch_row()) {
	$bantime = new DateTime($row[8]);
	$banexpires = new DateTime($row[13]);
	$banlen = generateDurationFromDates($bantime, $banexpires);
	$banrowtpl->setvar('BAN_STATUS', ($row[9] ? 'Unbanned':'Active'));
	//$banexpires = $bantime->add(new DateInterval('PT' . ($row[6]>=0?$row[6]:1999999999) . 'M'));
	if (!$row[9] && $banexpires < (New DateTime()))
		$banrowtpl->setvar('BAN_STATUS', 'Expired');
	$banrowtpl->setvar('UNBANNING_ADMIN', $row[11]?crossrefify($row[11],"adminckey"):"");
	$banrowtpl->setvar('UNBAN_TIME', ($row[10]?$row[10]:(strpos($row[3],"PERMA")?"":$banexpires->format("Y-m-d H:i:s")))); //if unbanned, show unban time, else, show expire time (unless a perma)
	$banrowtpl->setvar('BANNED_CKEY', crossrefify($row[0],'ckey'));
	$banrowtpl->setvar('BANNED_CID', crossrefify($row[1],'cid'));
	$banrowtpl->setvar('BANNED_IP', crossrefify($row[2],'ip'));
	$banrowtpl->setvar('USER_DETAILS', htmlspecialchars($row[0]).'<br/>'.$row[1].'<br/>'.$row[2]);
	$banrowtpl->setvar('BAN_TYPE', ($row[5]?$row[5]:FALSE));
	$banrowtpl->setvar('BAN_JOB', $row[5]);
	$banrowtpl->setvar('BAN_REASON', htmlspecialchars($row[4]));
	$banrowtpl->setvar('BAN_LENGTH', (strpos($row[3],"PERMA")!== FALSE?"Permanent":$banlen." Minute".($banlen==1?"":"s")));
	$banrowtpl->setvar('BANNING_ADMIN', crossrefify($row[7],"adminckey"));
	$banrowtpl->setvar('BAN_DATE', $row[8]);
	$banrowtpl->setvar('BAN_ID', $row[12]);
	$banrows .= $banrowtpl->process();
}

$bantabletpl = new template("bantable", array("BAN_ROWS" => $banrows));
$tpl->setvar('BANTABLE', $bantabletpl);
$thm->send($tpl);
?>