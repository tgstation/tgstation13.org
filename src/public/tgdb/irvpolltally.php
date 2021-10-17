<?php
namespace tgdb;
$totaltime = microtime(true);
require_once('include/include.php');
$user = auth(false);
if ($user[0] == "Optimumtact")
	$user[1] = "Chief Election Commissioner of /tg/Station13";
$tpl = new template('ipv', array(
	'USERCKEY'	 		=> $user[0], 
	'USERRANK' 			=> $user[1],
	'PLAYERONLYCHECKED'	=> '',
	'CONNECTIONSSTART'	=> '',
	'CONNECTIONSEND'	=> '',
	'CONNECTIONCOUNT'	=> '',
	'FIRSTSEEN'			=> '',
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
$adminonly = 'AND adminonly = false ';
if ($user[1])
	$adminonly = '';

$res = $mysqli->query('SELECT id FROM '.fmttable('poll_question').' WHERE NOW() > endtime '.$adminonly.'AND polltype = \'IRV\' AND id = '.$id.';');

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
$sqladminjoin = null;
if ($user[1]) {
	
	if (isset($_GET['firstseen']) && $_GET['firstseen']) {
		$firstseen = '\''.esc($_GET['firstseen']).'\'';
		$sqlwherea[] = 'p.firstseen < '.$firstseen;
		$tpl->setvar('FIRSTSEEN', htmlspecialchars($_GET['firstseen']));
	}
	if (isset($_GET['rankfilter'])) {
		switch ($_GET['rankfilter']) {
			case 'player':
				$sqlwherea[] = 'a.ckey IS NULL';
				$sqladminjoin = ' LEFT JOIN '.fmttable('admin').' AS a ON (v.ckey = a.ckey)'
				$tpl->setvar('RANK_PLAYERS', TRUE);
			break;
			case 'admin':
				$sqladminjoin = ' RIGHT JOIN '.fmttable('admin').' AS a ON (v.ckey = a.ckey)'
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
			$tpl->setvar('CONNECTIONCOUNT', $_GET['connectioncount']);
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

	if (count($sqlwherea)) {
		$tpl->setvar('PANELOPEN', 'in');
		$sqlwhere = " AND ".join(" AND ", $sqlwherea);
	}
}


$res = $mysqli->query('SELECT v.id, v.optionid, v.ckey, o.text FROM '.fmttable('poll_vote').' AS v LEFT JOIN '.fmttable('poll_option').' AS o ON (v.optionid = o.id) LEFT JOIN '.fmttable('player').' AS p ON (v.ckey = p.ckey)'.$sqladminjoin.' WHERE v.pollid = '.$id.$sqlwhere.' ORDER BY v.id;');
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
	}
	
	if (!isset($votes[$ckey]))
		$votes[$ckey] = array();
	
	$vote['CID'] = $cid;
	$vote['ID'] = $vid;
	$votes[$ckey][$cid] = $vote;
}
$res->free();

$candidatecmp = function ($a, $b) {
    return $b['VOTES'] - $a['VOTES'];
};

$votecmp = function ($a, $b) {
    return $a['ID'] - $b['ID'] ;
};

foreach ($votes as &$vote) {
	if (!uasort($vote, $votecmp))
		die ('Error sorting votes');
	$candidates[array_keys($vote)[0]]['VOTES']++;
}

if (!uasort($candidates, $candidatecmp))
	die ('Error sorting canidate list');

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

	foreach ($votes as &$vote) {
		if (array_keys($vote)[0] == $loser) {
			unset($vote[$loser]);
			$candidates[array_keys($vote)[0]]['VOTES']++;
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
