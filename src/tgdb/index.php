<?php 
namespace tgdb;
require_once("include/include.php");

$user = auth();

$tpl = new template("index", array(
	"USERCKEY"	=> crossrefify($user[0], 'adminckey'),
	"USERRANK"	=> $user[1],
));
$res = $mysqli->query("SELECT timestamp, text, adminckey, type FROM `".fmttable("messages")."` WHERE type = 'memo' ORDER BY timestamp DESC");

$memos = array();
while ($row = $res->fetch_assoc()) {
	$memo = array();
	$memo['DATE'] = $row['timestamp'];
	$memo['ADMIN'] = $row['adminckey'];
	$memo['MEMO'] = str_replace("\n", "\n<br/>", $row['text']);
	
	$memos[] = $memo;
}

$tpl->setvar('MEMOS', $memos);
$tpl->setvar('MEMOCOUNT', count($memos));
$tpl->setvar('MEMOTABLEOPEN', !count($memos) ? "collapse" : "in");

$res->free();

$thm = new theme("Home");

$thm->send($tpl);
?>