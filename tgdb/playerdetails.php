<?php
require_once("include/include.php");
navbar::setactive("player");

$user = auth();
if (!isset($_GET['ckey'])) {
	header('location: player.php');
	die();
}
$thm = new theme("Player Details");
$tpl = new template("playerdetails", array(
	"USERCKEY"	 			=>	$user[0], 
	"USERRANK" 				=>	$user[1],
	"PANELOPEN"				=>	"collapse",
	"SEARCHTYPEANYCHECKED"	=>	"checked",
	"SEARCHTYPEALLCHECKED"	=>	"",
	"CONRES"				=>	"",
	"BANRES"				=>	"",
	"PLAYERCKEY"			=>	htmlspecialchars($_GET['ckey'])
	));


$conrowtpl = new template("plyrow", array(	//initialize optional variables
	'DATECOL' 		=>	"",
	'IPCOL'			=>	""
));
$conrowdatecol = new template("plyrowdatecol");

$conrowipcol = new template("plyrowipcol"); 



//takes a array of 1 day of connections grouped by date then ckey then ip and spins it into 1 master row of the 
//	connection table with subrows for ckey and subrows for ip.
function processdate ($date) {
	global $conrowtpl,$conrowdatecol,$conrowipcol;
	$conrows = "";

	$datecount = 0;
	
	foreach ($date as $i)  
			$datecount += count($i);

	$conrowdatecol->setvar('ROWSPAN', $datecount);
	$conrowdatecol->setvar('DATE', $date[0][0][0]);
	$conrowtpl->setvar('DATECOL', $conrowdatecol);
	foreach ($date as $ip) {
		$conrowipcol->setvar('ROWSPAN', count($ip));
		$conrowipcol->setvar('IP', $ip[0][2]);
		$conrowtpl->setvar('IPCOL', $conrowipcol);
		foreach ($ip as $irow) {
			$conrowtpl->setvar('CID', $irow[3]);
			$conrowtpl->setvar('SERVER', $irow[1]);
			$conrowtpl->setvar('COUNT', $irow[4]);

			$conrow = $conrowtpl->process();

			$conrows .= $conrow."\n";

			$conrowtpl->resetvars(array(	//re-initialize optional variables
				'DATECOL' 		=>	"",
				'IPCOL'			=>	""
			));
		}
	}

	return $conrows;
}
$sqlwhere = "WHERE ckey = '".esc($_GET['ckey'])."'";
$res = $mysqli->query("SELECT DATE(datetime) AS `day`, serverip, ip, computerid, count(id) AS `count` FROM `".fmttable("connection_log")."`".$sqlwhere." GROUP BY day,serverip,ip,computerid ORDER BY day,ip");


//group same dates, then ips
//this assumes data is sorted by date, then ip.
$date = array(array()); //date holds an array of ips, each holding an array of rows

$ipi = 0;
$conrows = "";

//we will also keep a running tabs on a list of ckeys/ips/cids seen so we can display that as an easy list.

$cids = array();
$ips = array();
$rowcount = 0;
$connectioncount = 0;

//main sql processing loop

while ($row = $res->fetch_row()) {
	$connectioncount += $row[4];
	$rowcount++;
	
	
	if (!array_key_exists((string)$row[2],$ips))
		$ips[(string)$row[2]] = 0;
	$ips[(string)$row[2]] += $row[4];
	
	if (!array_key_exists((string)$row[3],$cids))
		$cids[(string)$row[3]] = 0;
	$cids[(string)$row[3]] += $row[4];	
	
	if (!count($date) || !count($date[0]) || !count($date[0][0])) { //first run, fill in current data
		$date = array(array($row));
		continue;
	}
		
	//date change, process current row set with the templates then reset state and continue
	if ($row[0] != $date[0][0][0]) {
		if (count($date)) //if we got an array in $date, lets turn it into a section of the connection table
			$conrows .= processdate($date)."\n";
		
		//reset state
		$date = array(array($row));
		$ipi = 0;
		continue;//no need to do any of the stuff down there
	}

	
	//look at the first row of the current ip of the current ckey to see what ip we are on
	if ($row[2] != $date[$ipi][0][2]) { //new ip, reset state and continue
		$date[][] = $row;
		$ipi++;
		continue;
	}
	
	//make a new row in the current ip of $date with all the data
	$date[$ipi][] = $row;
}

//now lets unset and free all the things
unset($row);
$res->free();

if (count($date)) //finish off the remaining day.
	$conrows .= processdate($date)."\n";
unset($date);



$contabletpl = new template("plytable");
$contabletpl->setvarr('CON_ROWS', $conrows);
unset($conrows);
$tpl->setvarr('CONTABLE', $contabletpl);
unset($contabletpl);

//now we build the other tables. 


$cidcontablerow = new template("cidcontablerow");
$cidcontablerows = "";
foreach ($cids as $cid=>$rounds) {
	$cidcontablerow->resetvars(array(
	'CID'		=>	$cid,
	'ROUNDS'	=>	$rounds
	));
	$cidcontablerows .= "\n".$cidcontablerow->process();
}
$tpl->setvar('CIDTABLE', new template('cidcontable',array('ROWS' => $cidcontablerows)));
$tpl->setvar('CIDCOUNT', count($cids));
$tpl->setvar('CIDTABLEOPEN', (count($cids) > 1 ? "collapse" : "in"));

$ipcontablerow = new template("ipcontablerow");
$ipcontablerows = "";
foreach ($ips as $ip=>$rounds) {
	$ipcontablerow->resetvars(array(
	'IP'		=>	$ip,
	'ROUNDS'	=>	$rounds
	));
	$ipcontablerows .= "\n".$ipcontablerow->process();
}

$tpl->setvar('IPTABLE', new template('ipcontable',array('ROWS' => $ipcontablerows)));
$tpl->setvar('IPCOUNT', count($ips));
$tpl->setvar('IPTABLEOPEN', (count($ips) > 1 ? "collapse" : "in"));

$tpl->setvar('ROWCOUNT', $rowcount);
$tpl->setvar('ROUNDCOUNT', $connectioncount);

//connection stuff is done, lets get ban information.



$thm->send($tpl);

?>