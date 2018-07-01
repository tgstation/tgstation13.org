<?php
namespace tgdb;
$totaltime = microtime(true);
require_once('include/include.php');
$user = auth(false);
if ($user[0] == "optimumtact")
	$user[1] = "Chief Election Commissioner of /tg/Station13";
$tpl = new template('ipv', array(
	'USERCKEY'	 		=> $user[0], 
	'USERRANK' 			=> $user[1],
	'PLAYERONLYCHECKED'	=> '',
	'CONNECTIONSSTART'	=> '',
	'CONNECTIONSEND'	=> '',
	'CONNECTIONCOUNT'	=> '',
	'FIRSTSEEN'			=> '',
	'JOBMINUTES'			=> '',
	'JOBNAME'			=> '',
	'PANELOPEN'			=> 'collapse',
	'ID'				=> 0
	));

function showpolllist() {
	global $mysqli, $tpl;
	$thm = new theme('Polls');
	$res = $mysqli->query('SELECT id, question FROM '.fmttable('poll_question').' WHERE NOW() > endtime AND polltype = \'IRV\' ORDER BY id DESC LIMIT 100;');
	$pollrows = array();
	while ($row = $res->fetch_assoc()) {
		$pollrow = array();
		$pollrow['ID'] = $row['id'];
		$pollrow['QUESTION'] =  htmlspecialchars($row['question']);
		$pollrows[] = $pollrow;
	}
	$res->free();
	$tpl->setvar('POLL_ROWS', $pollrows);
	$thm->send($tpl);
	die();
}

if (!isset($_GET['id']) || (int)($id = $_GET['id']+0) <= (int)0) {
	showpolllist();
}

//sanity

$sqlwherea = array();
if ($user[0] != 'MrStonedOne' && $user[0] !=  'optimumtact')
	$sqlwherea[] = 'NOW() > endtime';

if (!$user[1])
	$sqlwherea[] = 'adminonly = false';

$sqlwherea[] = 'polltype = \'IRV\'';
$sqlwherea[] = 'id = '.$id;
$sqlwhere = '';
if (count($sqlwherea))
	$sqlwhere = ' WHERE '.join(' AND ', $sqlwherea);

$res = $mysqli->query('SELECT id FROM '.fmttable('poll_question').$sqlwhere.';');

if (!$res->fetch_assoc())
	showpolllist();

$thm = new theme('Poll Results');
$tpl->setvar('ID', $id);
$firstseen = null;
$connectioncount = null;
$connectionsstart = null;
$connectionsend = null;
$playeronly = null;
$sqlwherea = array();
$sqlwhere = "";
$totalsort = false;
$lineartotal = false;
if (isset($_GET['totalsort'])) {
	$totalsort = true;
}
if (isset($_GET['lineartotal'])) {
	$lineartotal = true;
}
if ($user[1]) {
	if (isset($_GET['firstseen']) && $_GET['firstseen']) {
		$firstseen = '\''.esc($_GET['firstseen']).'\'';
		$sqlwherea[] = 'p.firstseen < '.$firstseen;
		$tpl->setvar('FIRSTSEEN', htmlspecialchars($_GET['firstseen']));
	}
	if (isset($_GET['rankfilter'])) {
		switch ($_GET['rankfilter']) {
			case 'player':
				$sqlwherea[] = 'p.lastadminrank IN (\'Player\', \'Coder\')';
				$tpl->setvar('RANK_PLAYERS', TRUE);
			break;
			case 'admin':
				$sqlwherea[] = 'p.lastadminrank NOT IN (\'Player\', \'Coder\')';
				$tpl->setvar('RANK_ADMINS', TRUE);
			break;
		}
	} 
	if (isset($_GET['connectionsstart']) && $_GET['connectionsstart']) {
		$connectionsstart = '\''.esc($_GET['connectionsstart']).'\'';
		$tpl->setvar('CONNECTIONSSTART', htmlspecialchars($_GET['connectionsstart']));
	}
	if (isset($_GET['connectionsend']) && $_GET['connectionsend']) {
		$connectionsend = '\''.esc($_GET['connectionsend']).'\'';
		$tpl->setvar('CONNECTIONSEND', htmlspecialchars($_GET['connectionsend']));
	}

	if (isset($_GET['connectioncount']) && $_GET['connectioncount']) {
		$connectioncount = (int)$_GET['connectioncount']+0;
		if ($connectioncount) {
			$tpl->setvar('CONNECTIONCOUNT', htmlspecialchars($_GET['connectioncount']));
			$sqlconnectionsubq = '(SELECT count(cc.ckey) FROM '.fmttable('connection_log').' AS cc WHERE cc.ckey = v.ckey';
			if ($connectionsstart && $connectionsend)
				$sqlconnectionsubq .= ' AND datetime BETWEEN '.$connectionsstart.' AND  '.$connectionsend;
			else if ($connectionsstart)
				$sqlconnectionsubq .= ' AND datetime > '.$connectionsstart;
			else if ($connectionsend)
				$sqlconnectionsubq .= ' AND datetime < '.$connectionsend;
			$sqlconnectionsubq .= ')';
			$sqlwherea[] = $sqlconnectionsubq.' >= '.$connectioncount;
		}
	}
	
	if (isset($_GET['jobname']) && $_GET['jobname'])
		$tpl->setvar('JOBNAME', htmlspecialchars($_GET['jobname']));
	if (isset($_GET['jobminutes']) && $_GET['jobminutes']) 
		$tpl->setvar('JOBMINUTES', htmlspecialchars($_GET['jobminutes']));
	
	if (isset($_GET['jobminutes']) && $_GET['jobminutes'] && isset($_GET['jobname']) && $_GET['jobname']) {
		$jobminutes = (int)$_GET['jobminutes']+0;
		if ($jobminutes > 0) {
			$jobs = explode('|', $_GET['jobname']);
			$jobsqllist;
			foreach ($jobs as $job) {
				if (!$jobsqllist)
					$jobsqllist = '("' . esc($job). '"';
				else
					$jobsqllist .= ', "' . esc($job). '"';
			}

			$jobsqllist .= ')';
			$sqljobsubq = '(SELECT SUM(j.minutes) FROM '.fmttable('role_time').' AS j WHERE j.job IN '.$jobsqllist. ' and j.ckey = v.ckey)';
			$sqlwherea[] = $sqljobsubq.' >= '. $jobminutes;
		}
	}

	if (count($sqlwherea))
		$tpl->setvar('PANELOPEN', 'in');
}
if (isset($_GET['skipid']) && $_GET['skipid']) {
	$skipid = (int)$_GET['skipid']+0;
	if ($skipid)
		$sqlwherea[] = 'v.optionid != '.$skipid;
}

if (count($sqlwherea)) 
	$sqlwhere = " AND ".join(" AND ", $sqlwherea);

$res = $mysqli->query('SELECT v.id, v.optionid, v.ckey, o.text FROM '.fmttable('poll_vote').' AS v LEFT JOIN '.fmttable('poll_option').' AS o ON (v.optionid = o.id) LEFT JOIN '.fmttable('player').' AS p ON (v.ckey = p.ckey) WHERE v.pollid = '.$id.$sqlwhere.' ORDER BY v.id;');
if (!$res) {
    die('Errormessage: '.$mysqli->error);
}
	
//record the votes.
$votes = array();
$candidates = array();
while ($row = $res->fetch_assoc()) {
	$vote = array();
	$ckey = $row['ckey'];
	$cid = $row['optionid'];
	$cname = $row['text'];
	$vid = $row['id'];
	if (!isset($candidates[$cid])) {
		$candidates[$cid] = array();
		$candidates[$cid]['CANDIDATE'] = $cname;
		$candidates[$cid]['VOTES'] = 0;
		$candidates[$cid]['VALUE'] = 0;
		$candidates[$cid]['VALUE_STR'] = '';
	}
	
	if (!isset($votes[$ckey]))
		$votes[$ckey] = array();
	
	$vote['CID'] = $cid;
	$vote['ID'] = $vid;
	$votes[$ckey][$cid] = $vote;
}
$res->free();

$candidatecmp = function ($a, $b) {
	global $totalsort;
	if ($totalsort)
		return $b['VALUE'] - $a['VALUE'];
	if ($b['VOTES'] == $a['VOTES']) {
		if ($b['VALUE'] == $a['VALUE'])
			return 0;
		$val = $b['VALUE'] - $a['VALUE'];
		while ($val < 1 && $val > -1) 
			$val *= 10;
		return $val;
	}
    return $b['VOTES'] - $a['VOTES'];
};

$votecmp = function ($a, $b) {
    return $a['ID'] - $b['ID'] ;
};
$initalvalue = count($candidates);
foreach ($votes as &$vote) {
	if (!uasort($vote, $votecmp))
		die ('Error sorting votes');
	$candidates[array_keys($vote)[0]]['VOTES']++;
	$value = $initalvalue;
	foreach ($vote as $pick => $array) {
		$candidates[$pick]['VALUE'] += $value;
		if ($lineartotal)
			$value -= 1;
		else
			$value /= 2;
	}
}

if (!uasort($candidates, $candidatecmp))
	die ('Error sorting canidate list');

foreach ($candidates as &$candidate) {
	$candidate['VALUE_STR'] = round($candidate['VALUE']);
}
$roundcount = 1;
$rounds = array();

//set up the first "round".
$round = array();
$round['ROUND_NUMBER'] = $roundcount++;
$round['IPV_ROUND_RES'] = $candidates;
$rounds[] = $round;

//kill off the bottom canidate and re-sort as long as there are mutiple candidates.
while (count($candidates) > 1) {
	$round = array();
	$round['ROUND_NUMBER'] = $roundcount++;
	$loser = array_keys($candidates)[count($candidates)-1];
	
	foreach($candidates as &$candidate) {
		$candidate['DIFF'] = 0;
	}
	
	foreach ($votes as &$vote) {
		if (array_keys($vote)[0] == $loser) {
			unset($vote[$loser]);
			$candidates[array_keys($vote)[0]]['VOTES']++;
			$candidates[array_keys($vote)[0]]['DIFF']++;
		} else {
			unset($vote[$loser]);
		}
	}
	unset($candidates[$loser]);
	if (!uasort($candidates, $candidatecmp))
		die ('Error sorting canidate list');
	$round['IPV_ROUND_RES'] = $candidates;
	$rounds[] = $round;
}

$tpl->setvar('IPV_ROUNDS', $rounds);
$thm->send($tpl);

?>