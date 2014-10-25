<?php
exec("cd ".dirname(__FILE__));

if (php_sapi_name() != "cli")
	return;


/* $endtime = time()+60; */

/* while (time() < $endtime) {
	$starttime = time(); */
	exec("php getserverdata.php");
/* 	if (10-(time()-$starttime) > 0)
		sleep(10-(time()-$starttime));
	
 */
/* } */
?>