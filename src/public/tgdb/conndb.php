<?php
namespace tgdb;
require_once("include/include.php");
navbar::setactive("cdb");
$user = auth();
$thm = new theme("Connection DB");
$tpl = new template("condb", array(
	"USERCKEY"	 			=>	crossrefify($user[0],'adminckey'), 
	"USERRANK" 				=>	$user[1],
 	"ADMINCKEY"		 		=>	"",
	"PLAYERCKEY" 			=>	"",
	"PLAYERCID" 			=>	"",
	"PLAYERIP"		 		=>	"",
	"PANELOPEN"				=>	"collapse in",
	"SEARCHTYPEANYCHECKED"	=>	"checked",
	"SEARCHTYPEALLCHECKED"	=>	"",
	"CONRES"				=>	""
	));
$conrestpl = new template("conres");

$conrowtpl = new template("conrow", array(	//initialize optional variables
	'DATECOL' 		=>	"",
	'CKEYCOL'		=>	"",
	'IPCOL'			=>	""
));
$conrowdatecol = new template("conrowdatecol");
$conrowckeycol = new template("conrowckeycol");
$conrowipcol = new template("conrowipcol"); 


$pdatetime = 0;
$pdatecounttime = 0;
$pdateprocesstime = 0;
$pdateresettime = 0;
$pdatetemplatetime = 0;
//takes a array of 1 day of connections grouped by date then ckey then ip and spins it into 1 master row of the 
//	connection table with subrows for ckey and subrows for ip.
function processdate ($date) {
	global $pdatetime, $pdatecounttime, $pdateprocesstime, $pdateresettime, $pdatetemplatetime;
	global $conrowtpl,$conrowdatecol,$conrowckeycol,$conrowipcol;
	$conrows = "";

	$datecount = 0;
	
	foreach ($date as $i) 
		foreach ($i as $k) 
			$datecount += count($k);

	$conrowdatecol->setvar('ROWSPAN', $datecount);
	$conrowdatecol->setvar('DATE', $date[0][0][0][0]);
	$conrowtpl->setvar('DATECOL', $conrowdatecol);
	foreach ($date as $ckey) {
		$ckeycount = 0;

		foreach ($ckey as $k) 
			$ckeycount += count($k);

		$conrowckeycol->setvar('ROWSPAN', $ckeycount);
		$conrowckeycol->setvar('CKEY', crossrefify($ckey[0][0][2],'ckey'));
		$conrowtpl->setvar('CKEYCOL', $conrowckeycol);
		foreach ($ckey as $ip) {

			$conrowipcol->setvar('ROWSPAN', count($ip));

			$conrowipcol->setvar('IP', crossrefify($ip[0][3],'ip'));
			$conrowtpl->setvar('IPCOL', $conrowipcol);
			foreach ($ip as $irow) {
				$conrowtpl->setvar('CID', crossrefify($irow[4],'cid'));
				$conrowtpl->setvar('SERVER', $irow[1]);
				$conrowtpl->setvar('COUNT', $irow[5]);

				$conrow = $conrowtpl->process();

				$conrows .= $conrow."\n";

				$conrowtpl->resetvars(array(	//re-initialize optional variables
					'DATECOL' 		=>	"",
					'CKEYCOL'		=>	"",
					'IPCOL'			=>	""
				));
			}
		}
	}
	return $conrows;
}


$sqlwherea = array();

if (isset($_GET['playerckey']) && $_GET['playerckey']) {
	$_GET['playerckey'] = keytockey($_GET['playerckey']);
	$playerckey = "'".esc($_GET['playerckey'])."'";
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
$limit = "LIMIT 50";
if (count($sqlwherea)) {
	//show the search panel
	$tpl->setvar('PANELOPEN', 'in');
	$sqlwhere = " WHERE ".join(" ".$sqlwheresep." ", $sqlwherea);
	$limit = "LIMIT 1000";
	$orderby = "asc";
} else if (!isset($_GET['showallconnections'])) {
	$thm->send($tpl);
	return;
}
$limit = "";
$t = timing::gettime();
$res = $mysqli->query("SELECT DATE(datetime) AS `day`, serverip, ckey, ip, computerid, count(id) AS `count` FROM `".fmttable("connection_log")."`".$sqlwhere." GROUP BY day,serverip,ckey,ip,computerid ORDER BY day,ckey,ip ".$orderby." ".$limit);
timing::tracktime("conndbSQL", $t);

//group same dates, then ckeys, then ips
//this assumes data is sorted by date, then ckey, then ip.
$date = array(array(array())); //date holds an array of ckeys,each holding an array of ips, each holding an array of rows
$ckeyi = 0;
$ipi = 0;
$conrows = "";

//we will also keep a running tabs on a list of ckeys/ips/cids seen so we can display that as an easy list.
$ckeys = array();
$cids = array();
$ips = array();
$rowcount = 0;
$connectioncount = 0;

//main sql processing loop

while ($row = $res->fetch_row()) {
	$connectioncount += $row[5];
	$rowcount++;
	
	if (!isset($ckeys[$row[2]]))
		$ckeys[(string)$row[2]] = 0;
	$ckeys[(string)$row[2]] += $row[5];
	
	if (!isset($ips[$row[3]]))
		$ips[(string)$row[3]] = 0;
	$ips[(string)$row[3]] += $row[5];
	
	if (!isset($cids[$row[4]]))
		$cids[(string)$row[4]] = 0;
	$cids[(string)$row[4]] += $row[5];	
	
	if (!count($date) || !count($date[0]) || !count($date[0][0]) || !count($date[0][0][0])) { //first run, fill in current data
		$date = array(array(array($row)));
		continue;
	}
		
	//date change, process current row set with the templates then reset state and continue
	if ($row[0] != $date[0][0][0][0]) {
		if (count($date)) //if we got an array in $date, lets turn it into a section of the connection table
			$conrows .= processdate($date)."\n";
		
		//reset state
		$date = array(array(array($row)));
		$ckeyi = 0;
		$ipi = 0;
		continue;//no need to do any of the stuff down there
	}
	
	//look at the first row of the first ip of the current ckey array to see what ckey we are on
	if ($row[2] != $date[$ckeyi][0][0][2]) { //new ckey, reset state and continue
		$date[] = array(array($row));
		$ckeyi++;
		$ipi = 0;
		continue;
	}
	
	//look at the first row of the current ip of the current ckey to see what ip we are on
	if ($row[3] != $date[$ckeyi][$ipi][0][3]) { //new ip, reset state and continue
		$date[$ckeyi][] = array($row);
		$ipi++;
		continue;
	}
	
	//make a new row in the current ip in the current cey of $date with all the data
	$date[$ckeyi][$ipi][] = $row;
}

//now lets unset and free all the things
unset($row);
$res->free();

if (count($date)) //finish off the remaining day.
	$conrows .= processdate($date)."\n";
unset($date);



$contabletpl = new template("contable");
$contabletpl->setvarr('CON_ROWS', $conrows);
unset($conrows);
$conrestpl->setvarr('CONTABLE', $contabletpl);
unset($contabletpl);

//now we build the other tables. 

$ckeycontablerow = new template("ckeycontablerow");
$ckeycontablerows = "";
foreach ($ckeys as $ckey=>$rounds) {
	$ckeycontablerow->resetvars(array(
	'CKEY'		=>	crossrefify($ckey,'ckey'),
	'ROUNDS'	=>	$rounds
	));
	$ckeycontablerows .= "\n".$ckeycontablerow->process();
}
$conrestpl->setvar('CKEYTABLE', new template('ckeycontable',array('ROWS' => $ckeycontablerows)));
$conrestpl->setvarr('CKEYCOUNT', count($ckeys));
$conrestpl->setvar('CKEYTABLEOPEN', (count($ckeys) > 1 ? "collapse" : "in"));

$cidcontablerow = new template("cidcontablerow");
$cidcontablerows = "";
foreach ($cids as $cid=>$rounds) {
	$cidcontablerow->resetvars(array(
	'CID'		=>	crossrefify($cid,'cid'),
	'ROUNDS'	=>	$rounds
	));
	$cidcontablerows .= "\n".$cidcontablerow->process();
}
$conrestpl->setvar('CIDTABLE', new template('cidcontable',array('ROWS' => $cidcontablerows)));
$conrestpl->setvar('CIDCOUNT', count($cids));
$conrestpl->setvar('CIDTABLEOPEN', (count($cids) > 1 ? "collapse" : "in"));

$ipcontablerow = new template("ipcontablerow");
$ipcontablerows = "";
foreach ($ips as $ip=>$rounds) {
	$ipcontablerow->resetvars(array(
	'IP'		=>	crossrefify($ip,'ip'),
	'ROUNDS'	=>	$rounds
	));
	$ipcontablerows .= "\n".$ipcontablerow->process();
}
$conrestpl->setvar('IPTABLE', new template('ipcontable',array('ROWS' => $ipcontablerows)));
$conrestpl->setvar('IPCOUNT', count($ips));
$conrestpl->setvar('IPTABLEOPEN', (count($ips) > 1 ? "collapse" : "in"));

$conrestpl->setvar('ROWCOUNT', $rowcount);
$conrestpl->setvar('CONNECTIONCOUNT', $connectioncount);

$tpl->setvarr('CONRES', $conrestpl);
$thm->send($tpl);

?>