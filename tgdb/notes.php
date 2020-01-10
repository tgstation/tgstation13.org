<?php
namespace tgdb;
use DateTime;
require_once("include/include.php");
navbar::setactive("note");

$user = auth();

$tpl = new template("notes", array(
	"USERCKEY"	 	=>	crossrefify($user[0],'adminckey'), 
	"USERRANK" 		=>	$user[1],
	"ADMINCKEY" 	=>	"",
	"PLAYERCKEY" 	=>	"",
	"ADMINCKEY" 	=>	"",
	"TEXT"	 		=>	"",
	"PANELOPEN"		=>	"collapse in",
	"SEARCHTYPEANYCHECKED"	=>	"checked",
	"SEARCHTYPEALLCHECKED"	=>	""
	));
	
$thm = new theme("Notes");

$sqlwherea = array();
if (isset($_GET['adminckey']) && $_GET['adminckey']) {
	$_GET['adminckey'] = keytockey($_GET['adminckey']);
	$adminckey = "'".esc($_GET['adminckey'])."'";
	$sqlwherea[] = "adminckey LIKE ".$adminckey;
	$tpl->setvar('ADMINCKEY', htmlspecialchars($_GET['adminckey']));
}

if (isset($_GET['playerckey']) && $_GET['playerckey']) {
	$_GET['playerckey'] = keytockey($_GET['playerckey']);
	$playerckey = "'".esc($_GET['playerckey'])."'";
	$sqlwherea[] = "ckey LIKE ".$playerckey;
	$tpl->setvar('PLAYERCKEY', htmlspecialchars($_GET['playerckey']));
}

if (isset($_GET['text']) && $_GET['text']) {
	$text = "'%".esc($_GET['text'])."%'";
	$sqlwherea[] = "notetext LIKE ".$text."";
	$tpl->setvar('TEXT', htmlspecialchars($_GET['text']));
}

$sqlwheresep = "OR";
if (isset($_GET['searchtype']) && $_GET['searchtype'] == "all") {
	$tpl->setvar("SEARCHTYPEANYCHECKED", "");
	$tpl->setvar("SEARCHTYPEALLCHECKED", "checked");
	$sqlwheresep = "AND";
}

$sqlwhere = "";
if (count($sqlwherea)) {
	$sqlwhere = " WHERE ".join(" ".$sqlwheresep." ", $sqlwherea);
}
$limit = 200;
if (isset($_GET['limit']))
	$limit = 0+$_GET['limit']+0;
$res = $mysqli->query("SELECT id, ckey, timestamp, notetext, adminckey, server FROM ".fmttable("notes").$sqlwhere." ORDER BY timestamp DESC LIMIT ".$limit.";");
$notes = array();
while ($row = $res->fetch_assoc()) {
	$note = array();
	$note['ID'] = $row['id'];
	$note['CKEY'] = crossrefify($row['ckey'], 'ckey');
	$note['DATE'] = $row['timestamp'];
	$note['ADMIN'] = crossrefify($row['adminckey'], 'adminckey');
	$note['SERVER'] = $row['server'];
	$note['NOTE'] = $row['notetext'];
	$notes[] = $note;
}
$tpl->setvar('NOTES', $notes);

$thm->send($tpl);
?>