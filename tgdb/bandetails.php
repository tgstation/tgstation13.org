<?php
namespace tgdb;
use DateTime;
require_once("include/include.php");

$user = auth();
navbar::setactive("ban");
$tpl = new template("bandetails", array(
	"USERCKEY"	 	=>	crossrefify($user[0],'adminckey'), 
	"USERRANK" 		=>	$user[1]));

$thm = new theme("Bans");

$searchquery = false;
if (isset($_GET['playerckey']) || isset($_GET['playercid']) || isset($_GET['playerip']) || isset($_GET['adminckey'])) {
	$searchquery = true;
}
if (!isset($_GET['id'])) {
	if ($searchquery)
		header("location: bans.php?".$_SERVER["QUERY_STRING"]);
	$tpl->setvar("ERROR_MSG", "No id given.");
	$thm->send($tpl);
	die();
}
$id = $_GET['id']+0;
if ((int)$id <= (int)0) {
	$tpl->setvar("ERROR_MSG", "Invalid id given.");
	$thm->send($tpl);
	die();
}
	
	$res = $mysqli->query("SELECT * FROM ".fmttable("ban")." WHERE id = ".$id." LIMIT 1;");
	
	if ($row = $res->fetch_assoc()) {
		foreach ($row as $name=>$value) {
			switch ($name) {
				case "who":
				case "adminwho":
					$wholist = array_map('trim', explode(',', $value));;
					$whotpl = new template('wholist');
					$value = "";
					
					sort($wholist);
					foreach ($wholist as $ckey) {
						$whotpl->setvar('CKEY', trim($ckey));
						$value .= $whotpl->process();
					}
					break;
				case "duration":
					$value = $value." Minute".($value==1?"":"s");
				case "edits":
					$value = str_replace(array('<cite>', '</cite>'), array('<br><cite>', '</cite><br>'), $value);
					$value = str_replace('<br>', '<br/>', $value);
					
					break;
				case "ckey":
					$value = crossrefify($value,'ckey');
					break;
				case "computerid":
					$value = crossrefify($value,'cid');
					break;
				case "ip":
					$value = crossrefify($value,'ip');
					break;
				case "a_ckey":
				case "unbanned_ckey":
					$value = crossrefify($value,'adminckey');
					break;
				default:
					$value = htmlspecialchars($value);
					break;
			}
			if ($value)
				$tpl->setvar(strtoupper($name), $value);
			
		}
		$expirebantime = new DateTime($row['expiration_time']);
		$bantime = new DateTime($row['bantime']);
		if ($row['unbanned'])
			$tpl->setvar("BAN_STATUS", 'Unbanned');
		else if ($expirebantime < new datetime() && (int)$row['duration'] >= 0 && strpos($row['bantype'],"PERMA") === FALSE)
			$tpl->setvar("BAN_STATUS", 'Expired');
		else
			$tpl->setvar("BAN_STATUS", 'Active');
		
		
		if (strpos($row['bantype'],"PERMA") === FALSE) {
			$realbantime = generateDurationFromDates($bantime,$expirebantime);
			$tpl->setvar("REAL_BAN_TIME", $realbantime . " Minute".($realbantime==1?"":"s"));
		} else {
			$tpl->setvar("REAL_BAN_TIME", "Permanent");
		}
			
	
	} else {
		$tpl->setvar("ERROR_MSG", "id not found!");
		$thm->send($tpl);
		die();
	}

$thm->send($tpl);
?>