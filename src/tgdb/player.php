<?php
namespace tgdb;
$totaltime = microtime(true);
require_once("include/include.php");
navbar::setactive("player");
$user = auth();
$thm = new theme("Player Lookup");
$tpl = new template("player", array(
	"USERCKEY"	 			=>	crossrefify($user[0],'adminckey'), 
	"USERRANK" 				=>	$user[1],
 	"ADMINCKEY"		 		=>	"",
	"PLAYERCKEY" 			=>	"",
	"PLAYERCID" 			=>	"",
	"PLAYERIP"		 		=>	"",
	"SEARCHTYPEANYCHECKED"	=>	"checked",
	"SEARCHTYPEALLCHECKED"	=>	"",
	"PLAYERRES"				=>	""
	));
$playerrestpl = new template("playerres", array(
	"QUERY"		=> $_SERVER["QUERY_STRING"]
	));


//takes a array of 1 day of connections grouped by date then ckey then ip and spins it into 1 master row of the 
//	connection table with subrows for ckey and subrows for ip.

$sqlwherea = array();

if (isset($_GET['playerckey']) && $_GET['playerckey']) {
	$playerckey = "'".esc(keytockey($_GET['playerckey']))."'";
	$sqlwherea[] = "ckey LIKE ".$playerckey;
	$tpl->setvar('PLAYERCKEY', htmlspecialchars($_GET['playerckey']));
}

if (isset($_GET['playercid']) && $_GET['playercid']) {
	$playercid = "'".esc($_GET['playercid'])."'";
	$sqlwherea[] = "computerid LIKE ".$playercid;
	$tpl->setvar('PLAYERCID', htmlspecialchars($_GET['playercid']));
}

if (isset($_GET['playerip']) && $_GET['playerip']) {
	$playerip = "'".esc($_GET['playerip'])."'";
	$sqlwherea[] = "ip LIKE ".$playerip;
	$tpl->setvar('PLAYERIP', htmlspecialchars($_GET['playerip']));
}

$sqlwheresep = "OR";
if (isset($_GET['searchtype']) && $_GET['searchtype'] == "all") {
	$tpl->setvar("SEARCHTYPEANYCHECKED", "");
	$tpl->setvar("SEARCHTYPEALLCHECKED", "checked");
	$sqlwheresep = "AND";
}

$sqlwhere = "";
$orderby = "desc";
$limit = "LIMIT 100";
if (count($sqlwherea)) {
	//show the search panel
	$tpl->setvar('PANELOPEN', 'in');
	$sqlwhere = " WHERE ".join(" ".$sqlwheresep." ", $sqlwherea);
	$limit = "";
	$orderby = "asc";
} else {
	$thm->send($tpl);
	return;
}
$res = $mysqli->query("SELECT ckey, count(*) AS `count` FROM `".fmttable("connection_log")."`".$sqlwhere." GROUP BY ckey");

$ckeys = array();
//sql processing loop

while ($res && $row = $res->fetch_row()) {
	$ckeys[(string)$row[0]] += $row[1];
}

if (!count($ckeys)) {
	$thm->send($tpl);
	return;
}

if (count($ckeys) == 1 && (!isset($_GET['showres']))) {
	header("location: playerdetails.php?ckey=".urlencode(array_keys($ckeys)[0]));
	die();
}

$ckeycontablerow = new template("ckeycontablerow");
$ckeycontablerows = "";
foreach ($ckeys as $ckey=>$rounds) {
	$ckeycontablerow->resetvars(array(
	'CKEY'		=>	crossrefify($ckey,'ckey'),
	'ROUNDS'	=>	$rounds
	));
	$ckeycontablerows .= "\n".$ckeycontablerow->process();
}
$playerrestpl->setvar('CKEYTABLE', new template('ckeycontable',array('ROWS' => $ckeycontablerows)));
$playerrestpl->setvar('CKEYCOUNT', count($ckeys));
$tpl->setvar('PLAYERRES', $playerrestpl);
$thm->send($tpl);
?>