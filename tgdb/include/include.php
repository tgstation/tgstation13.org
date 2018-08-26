<?php
namespace tgdb;
use DateTime;
use DateInterval;
require_once("config.php");

require_once("include/classes/timing.php");
$phptotaltime = timing::gettime();
require_once("include/classes/theme.php");
require_once("include/classes/template.php");
require_once("include/classes/navbar.php");

define("LOGIN_ERROR_NOTLOGGEDIN", 1);
define("LOGIN_ERROR_NOTLINKED", 2);
define("LOGIN_ERROR_TOOOLD", 3);

//backward compatibility.
if (!isset($tgdbconfig)) {
	if (!isset($config))
		die("Error: Config missing");
	$tgdbconfig = $config;
	unset($config);
}


$mysqli = get_new_mysqli();

//to lower the window that any exploit that exposes global variables has to get sql database information.
//unset($tgdbconfig['sql']['password']);


if ($mysqli->connect_errno) {
    die ("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}
function get_new_mysqli() {
	global $tgdbconfig;
	static $tgdbpassword;
	//To lower the window that any exploit that exposes global variables has to get sql database information, we remove the password and store it statically in this function. (of course, an exploit could exist that exposes static vars, but thats rarely a target)
	if (isset($tgdbconfig['sql']['password'])) {
		$tgdbpassword = $tgdbconfig['sql']['password'];
		unset($tgdbconfig['sql']['password']);
	}
	$mysqli = new \mysqli();
	$mysqli->real_connect('p:'.$tgdbconfig['sql']['addr'], $tgdbconfig['sql']['user'], $tgdbpassword, $tgdbconfig['sql']['db'], (is_int($tgdbconfig['sql']['port']) ? $tgdbconfig['sql']['port'] : null), (!is_int($tgdbconfig['sql']['port']) ? $tgdbconfig['sql']['port'] : null), MYSQLI_CLIENT_COMPRESS);
	return $mysqli;
}
//escapes a string for mysql, mainly used for shortening reasons
//**does not enclose string in quotes**
function esc ($text) {
	global $mysqli;
	return $mysqli->real_escape_string($text);
}

//returns string with $config['sql']['tableprefex'] prepended to it
function fmttable($table) {
	global $tgdbconfig;
	if (isset($tgdbconfig['sql']['tableprefix']) && !empty($tgdbconfig['sql']['tableprefix']))
		return $tgdbconfig['sql']['tableprefix'].$table;
	
	return $table;
}
//Loads up active auth module and attempts to figure out who the user is.
//option bool to prevent loading the login page on auth failure(only used for login page)
function auth ($required = true) {
	global $tgdbconfig;
	if ($tgdbconfig['auth'] == "forum")
		return auth_forums($required);

	return auth_ip($required);
}

function auth_ip($required = true) {
    global $mysqli;

    $res = $mysqli->query("SELECT a.ckey, a.rank as lastadminrank FROM `".fmttable("admin")."` AS `a` INNER JOIN `".fmttable("player")."` AS `p` ON a.ckey = p.ckey WHERE p.lastseen > DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND p.ip = '".esc(ip2long($_SERVER['REMOTE_ADDR']))."'");
    
    if (!$res) {
        die ("Failed to authenticate (" . $mysqli->errno . ") " . $mysqli->error);
    }
    
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
function auth_forums($required) {
	global $tgdbconfig, $mysqli;
	global $db, $cache, $config, $user, $phpbb_root_path, $phpEx; //phpbb globals.
	if (!isset($tgdbconfig['authsettings']['forumpath']) || !isset($tgdbconfig['authsettings']['forumurl']))
		return auth_ip($required);
	$redirecturl = urlencode($_SERVER['REQUEST_URI']);
	//stuff phpbb wants defined.
	if (!defined('phpbb_root_path'))
		define('PHPBB_ROOT_PATH', $tgdbconfig['authsettings']['forumpath']);
	$phpbb_root_path = PHPBB_ROOT_PATH;
	define('IN_PHPBB', true);
	
	$phpEx = substr(strrchr(__FILE__, '.'), 1);
	
	include_once($phpbb_root_path.'common.'.$phpEx); //we include the phpbb frame work
	global $user, $db;
	$user->session_begin(); //now we let phpbb do all the fancy work of figuring out who the fuck this are.
	$userid = (int)$user->data['user_id'];
	$usertype = $user->data['user_type'];
	
	//users aren't logged in if their account is suspended or they are a special "bot/web crawler" account
	//they also aren't if their userid is 0 or a negative 
	if($userid <= 1 || $usertype == 1 || $usertype == 2) {
		if ($required) {
			$tpl = new template("auth_login", array("REDIRECTURL" => $redirecturl, "FORUMURL" => $tgdbconfig['authsettings']['forumurl']));
			echo $tpl->process();
			die();
		}
		return array("", "");
	}
	
	//we use phpbb's sql engine here so we can save a config option (there is no phpbb internal way to get this data that doesn't also format it for html output in an odd way)
	$sql = "SELECT `pf_byond_username` FROM `".PROFILE_FIELDS_DATA_TABLE."` WHERE user_id = ".$userid;
	$result = $db->sql_query($sql);
	$key = $db->sql_fetchfield('pf_byond_username');
	$db->sql_freeresult($result);
	if (!$key) {
		if ($required) {
			$tpl = new template("auth_link", array("REDIRECTURL" => $redirecturl, "FORUMURL" => $tgdbconfig['authsettings']['forumurl']));
			echo $tpl->process();
			die();
		}
		return array("", "");
	}
	$ckey = keytockey($key, false);
	//alright, we have their ckey now. 
	if (in_array($ckey,$tgdbconfig['authsettings']['blockedckeys'])) {
		if ($required) {
			$tpl = new template("auth_noaccess");
			echo $tpl->process();
			die();
		}
		return array($key, "");
	}
	if (in_array($ckey,$tgdbconfig['authsettings']['whitelistedckeys'])) {
		return array($key, "Exempt");
	}
	
	$ttl = 168;
	
	if (isset($config['authsettings']['validitytime']) && (int)$config['authsettings']['validitytime'])
		$ttl = (int)$config['authsettings']['validitytime'];
	
	$res = $mysqli->query("SELECT lastadminrank, lastseen FROM `".fmttable("player")."` WHERE ckey = '".esc($ckey)."'");
	
	if (!$res) {
		if ($required) {
			die ("Failed to authenticate (" . $mysqli->errno . ") " . $mysqli->error);
		}
		return array($key, "");
	}
	if (!$row = $res->fetch_assoc()) {
		if ($required) {
			$tpl = new template("auth_noaccess");
			echo $tpl->process();
			die();
		}
		return array($key, "");
	}
	$rank = $row['lastadminrank'];

	if (in_array(strtolower($rank), $tgdbconfig['authsettings']['excludedranks'])) {
		if ($required) {
			$tpl = new template("auth_noaccess");
			echo $tpl->process();
			die();
		}
		return array($key, "");
	}
	
	$lastseen = new DateTime($row['lastseen']);
	if ($lastseen < (New DateTime())->sub(new DateInterval('PT'.$ttl.'H'))) {
		if ($required) {
			$tpl = new template("auth_reauth", array("REDIRECTURL" => $redirecturl, "FORUMURL" => $tgdbconfig['authsettings']['forumurl']));
			echo $tpl->process();
			die();
		}
		return array($key, "");
	}
	return array($key, $rank);
}
function is_assoc($var)
{
		//casting to array and checking with === takes 23% of the time that is_array() does
		//also returns true if the array is empty
        return (((array)$var === $var) && (!count($var) || array_diff_key($var,array_keys(array_keys($var)))));
}

function keytockey ($key, $keepmysqlwildcards = true) {
	if ($keepmysqlwildcards)
		return strtolower(preg_replace('/[^a-zA-Z0-9@%]/', '', $key));
	else 
		return strtolower(preg_replace('/[^a-zA-Z0-9@]/', '', $key));
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
//takes two DateTime dates and returns the amount of minutes between them.
function generateDurationFromDates($start, $end) {
	$minutes = 0;
	$diff = $start->diff($end);
	$minutes += $diff->days * 24 * 60;
	$minutes += $diff->h * 60;
	$minutes += $diff->i;
	return $minutes;
}









?>
