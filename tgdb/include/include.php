<?php

require_once("config.php");
require_once("include/classes/timing.php");
$phptotaltime = timing::gettime();
require_once("include/classes/theme.php");
require_once("include/classes/template.php");
require_once("include/classes/navbar.php");




$mysqli = new mysqli($config['sql']['addr'], $config['sql']['user'], $config['sql']['password'] , $config['sql']['db'], (is_int($config['sql']['port']) ? $config['sql']['port'] : null), (!is_int($config['sql']['port']) ? $config['sql']['port'] : null));



if ($mysqli->connect_errno) {
    die ("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

//escapes a string for mysql, mainly used for shortening reasons
//**does not enclose string in quotes**
function esc ($text) {
	global $mysqli;
	return $mysqli->real_escape_string($text);
}

//returns string with $config['sql']['tableprefex'] prepended to it
function fmttable($table) {
	global $config;
	if (isset($config['sql']['tableprefix']) && !empty($config['sql']['tableprefix']))
		return $config['sql']['tableprefix'].$table;
	
	return $table;
}
//Loads up active auth module and attempts to figure out who the user is.
//option bool to prevent loading the login page on auth failure(only used for login page)
function auth($required = true) {
	global $mysqli;
	$res = $mysqli->query("SELECT ckey,lastadminrank FROM `".fmttable("player")."` where lastadminrank != 'player' and lastseen > DATE_SUB(CURDATE(),INTERVAL 1 DAY) and ip = '".esc($_SERVER['REMOTE_ADDR'])."'");

	if ($row = $res->fetch_row()) {
		$user = array($row[0],$row[1]);
		return $user;
	}
	
	if ($required) {
		include("login.php");
		die();
	}
	return array("", "");
}

function is_assoc($var)
{
		//casting to array and checking with === takes 23% of the time that is_array() does
		//also returns true if the array is empty
        return (((array)$var === $var) && (!count($var) || array_diff_key($var,array_keys(array_keys($var)))));
}

function crossrefify ($item, $type, $vars = array()) {
	$crossrefed = '';
	$tpl = null;
	switch ($type) {
		case 'adminckey':
			$tpl = new template('crossrefadminckey', $vars);
			$tpl->setvar('ITEM', $item);
		break;
		case 'ckey':
			$tpl = new template('crossrefckey', $vars);
			$tpl->setvar('ITEM', $item);
		break;
		case 'cid':
			$tpl = new template('crossrefcid', $vars);
			$tpl->setvar('ITEM', $item);
		break;
		case 'ip':
			$tpl = new template('crossrefip', $vars);
			$tpl->setvar('ITEM', $item);
		break;
	}
	return $tpl;
}

?>