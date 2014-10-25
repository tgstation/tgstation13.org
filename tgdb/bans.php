<?php
require_once("include/include.php");
navbar::setactive("ban");

$user = auth();

$tpl = new template("bans", array(
	"USERCKEY"	 	=>	$user[0], 
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
	$adminckey = "'".esc($_GET['adminckey'])."'";
	$sqlwherea[] = "a_ckey LIKE ".$adminckey;
	$tpl->setvar('ADMINCKEY', htmlspecialchars($_GET['adminckey']));
}

//player search stuff, uses its own array because it needs to OR its search in () then AND it with the admin search.
$playersearcha = array();
if (isset($_GET['playerckey']) && $_GET['playerckey']) {
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

$res = $mysqli->query("SELECT ckey,computerid,ip,bantype,reason,job,duration,a_ckey FROM ".fmttable("ban").$sqlwhere." ORDER BY id DESC LIMIT 250;");

$banrowtpl = new template("banrow");
$banrows = "";
while ($row = $res->fetch_row()) {
	$banrowtpl->resetvars();
	$banrowtpl->setvar('USER_DETAILS', htmlspecialchars($row[0]).'<br/>'.$row[1].'<br/>'.$row[2]);
	$banrowtpl->setvar('BAN_TYPE', $row[5]."<br/>".$row[3]);
	$banrowtpl->setvar('BAN_REASON', htmlspecialchars($row[4]));
	$banrowtpl->setvar('BAN_LENGTH', $row[6]);
	$banrowtpl->setvar('BANNING_ADMIN', $row[7]);
	
	$banrows .= $banrowtpl->process();
}

$bantabletpl = new template("bantable", array("BAN_ROWS" => $banrows));
$tpl->setvar('BANTABLE', $bantabletpl->process());
$thm->send($tpl);
?>