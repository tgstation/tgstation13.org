<?php
exec("cd ".dirname(__FILE__));

if (php_sapi_name() != "cli")
	return;


$endtime = time()+60;

while (time() < $endtime) {
	$starttime = time();
	exec("php getserverdata.php");
 	if (1-(time()-$starttime) > 0)
		sleep(1-(time()-$starttime));
	
 
}
?>
