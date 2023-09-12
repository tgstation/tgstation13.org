<?php
exec("cd ".dirname(__FILE__));

if (php_sapi_name() != "cli")
	return;


$endtime = time()+60;

while (time() < $endtime) {
	$starttime = time();
	exec("php getserverdataasync.php");
	$sleeptime = 4-(max(time()-$starttime, 4));
 	if ($sleeptime > 0)
		sleep($sleeptime);
	
 
}
?>