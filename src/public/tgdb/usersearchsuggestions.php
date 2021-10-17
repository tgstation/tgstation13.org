<?php
namespace tgdb;
$totaltime = microtime(true);
require_once("include/include.php");
auth();
$ckeytosearch = esc(keytockey($_GET['ckey'],TRUE));

//connections
$connection_time = microtime(true);
$exactlink = $mysqli;
$startlink = get_new_mysqli();
$wildcardlink = get_new_mysqli();
$connection_time = microtime(true) - $connection_time;

$query_time = microtime(true);
//exact results
$sqlwhere = " WHERE ckey = '".$ckeytosearch."'";
$exactlink->query("SELECT ckey FROM `".fmttable("player")."`".$sqlwhere." order by lastseen", MYSQLI_ASYNC);

//results where the username matches from the begaining
$sqlwhere = " WHERE ckey like '".$ckeytosearch."%'";
$startlink->query("SELECT ckey FROM `".fmttable("player")."`".$sqlwhere." ORDER BY lastseen DESC limit 15", MYSQLI_ASYNC);

//results where the username matches in any way
$sqlwhere = " WHERE ckey like '%".$ckeytosearch."%'";
$wildcardlink->query("SELECT ckey FROM `".fmttable("player")."`".$sqlwhere." ORDER BY lastseen DESC limit 15", MYSQLI_ASYNC);
$query_time = microtime(true) - $query_time;

//store an array of all of our queries (as well as a sub array with results)
$alllinks = array(array($exactlink, array()), array($startlink, array()), array($wildcardlink, array()));

$process_time = microtime(true);
$processed = 0;
$loopcount = 0;
$ckeys = array();
$totalcount = 0;
//loop thru getting results until we have enough or we loop too much.
do {
	//output array with queries to check
	$readlinks = $erroredlinks = $rejectedlinks = array();

	//Fill it up.
	foreach($alllinks as $link) {
		$readlinks[] = $erroredlinks[] = $rejectedlinks[] = $link[0];
	}

	//wait at most 0.5 seconds for all of those queries to return results.
	mysqli_poll($readlinks, $erroredlinks, $rejectedlinks, 0, 50000);

	//process the ones that returned results.
	foreach ($readlinks as $link) {
		//find it in the master array
		$index = -1;
		foreach ($alllinks as $i => $L) {
			if ($L[0] == $link) {
				$index = $i;
				break;
			}
		}
		//not found
		if ($index === -1)
			continue;
		//grab the data
		$res = $link->reap_async_query();
		while ($res && $row = $res->fetch_row()) {
			$alllinks[$index][1][] = $row[0]; //shove it in.
		}
		$processed++;
		$res->close();
	}
	foreach ($erroredlinks as $link)
		$processed++;
	$loopcount++;
	

	foreach ($alllinks as $link)
		$totalcount += count($link[1]);

} while ($loopcount < 10 && $processed < count($alllinks) && $totalcount < 20);

//loop thru the queries's data and sort it into $ckeys
foreach ($alllinks as $link)
	foreach ($link[1] as $ckey)
		$ckeys[] = $ckey;
$process_time = microtime(true) - $process_time;
$totaltime = microtime(true) - $totaltime;
//echo json_encode(array("c:$connection_time", "q:$query_time", "p:$process_time", "t:$totaltime"));

echo json_encode(array_unique($ckeys));










