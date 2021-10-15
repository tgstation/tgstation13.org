<?php 
    // Connection settings:
     
    $servers = Array();
     
    /*
    Example: (copy paste this to somewhere AFTER the comment and fill with your own information)
    If you have multiple servers, list them one after the other. Change the id to the server name used on the indexpage etc. tho.
     
    "address" and "port": is the ip or url you use to connect: Normally you see
    byond://123.123.123.123:56372. The 123.123.123.123 part is the address, the 56372
    part is the port. Fill with your own information, obviously. If you use an url
    and you connect to something like byond://game.mysite.com:1234, then
    game.mysite.com is the address and 1234 the port
    "servername": is just a string that gets written on the image. Can be
    pretty much anything
     
    //My Server
    $servers['ftl13'] = Array();
    $servers['ftl13']["address"] = "192.168.0.100";
    $servers['ftl13']["port"] = 56372;
    $servers['ftl13']["servername"] = "SS13: My Server";
     
    */
     
    //Copy paste the code above to after this line
     
    //Bagil
    $servers['bagil'] = Array();
    $servers['bagil']['address'] = 'bagil.tgstation13.org';
    $servers['bagil']['port'] = 2337;
	$servers['bagil']['servername'] = 'Basil [US-West]';
	//$servers['bagil']['popcap'] = 90;
	if (rand(0,25) === 0)
		$servers['bagil']['servername'] = 'Bagil [US-West]';
	//$servers['bagil']['errortext'] = "Bueller? Bueller?\nBueller? Bueller?";
	//if (rand(0,2) === 2)
	//	$servers['bagil']['servername'] = 'Bagil: THUNDERDOME!';
    
	//Sybil
    $servers['sybil'] = Array();
    $servers['sybil']['address'] = 'sybil.tgstation13.org';
    $servers['sybil']['port'] = 1337;
    $servers['sybil']['servername'] = 'Sybil [US-West]';
	if (rand(0,25) === 0)
		$servers['sybil']['servername'] = 'Sigil [US-West]';
	else if (rand(0,50) === 0)
		$servers['sybil']['servername'] = 'Sybil [Roleplay/US-West]';
	//$servers['sybil']['popcap'] = 70;
	//$servers['sybil']['errortext'] = "Server Starting...";


	//Sybil
    $servers['sybil2'] = Array();
    $servers['sybil2']['address'] = 'manuel.tgstation13.org';
    $servers['sybil2']['port'] = 1447;
    $servers['sybil2']['servername'] = 'Manuel [Roleplay/US-West]';
	if (rand(0,100) === 0)
		$servers['sybil2']['servername'] = 'Sybil-2 [Roleplay/US-Wes]';
	//$servers['sybil2']['popcap'] = 70;
	//$servers['sybil2']['errortext'] = "Server Starting...";

	$servers['eventhall'] = Array();
    $servers['eventhall']['address'] = 'events.tgstation13.org';
    $servers['eventhall']['port'] = 4337;
    $servers['eventhall']['servername'] = 'Event Hall [EU]';
	//$servers['eventhall']['servername'] = 'Summer Ball [EU-DE]';
	$servers['eventhall']['errortext'] = "No events currently \nplanned";
	//$servers['eventhall']['errortext'] = "Backup Summer Ball Server";
	//$servers['eventhall']['popcap'] = 300;
	//$servers['eventhall']['eventcolors'] = true;
	//$servers['eventhall']['errortext'] = "Saturday the 20th\n3pm EDT|8pm GMT+1";

	$servers['eventhallus'] = Array();
    $servers['eventhallus']['address'] = 'bagil.tgstation13.org';
    $servers['eventhallus']['port'] = 4447;
    //$servers['eventhallus']['servername'] = '/tg/Station GameShow';
	$servers['eventhallus']['servername'] = 'Event Hall [US-West]';
	$servers['eventhallus']['errortext'] = "No events currently planned";
	//$servers['eventhallus']['errortext'] = "Down for Maintenance";
	//$servers['eventhallus']['popcap'] = 300;
	//$servers['eventhallus']['eventcolors'] = true;
	//$servers['eventhallus']['errortext'] = "Sunday the 27th \n 4PM EST 9PM GMT+1 1PM PST";

	
	$servers['terry'] = Array();
    $servers['terry']['address'] = 'terry.tgstation13.org';
    $servers['terry']['port'] = 3336;
	$servers['terry']['servername'] = 'Terry [EU]';
	if (rand(0,10) === 0)
		$servers['terry']['servername'] = ''.array_rand(array('Terry' => '', 'Larry' => '', 'Garry' => '', 'Jerry' => '')).' [EU]';
	//$servers['terry']['errortext'] = "Bueller? Bueller?\nBueller? Bueller?";
	//$servers['terry']['popcap'] = 90;
	
	$servers['dmca'] = Array();
    $servers['dmca']['address'] = 'tgmc.tgstation13.org';
    $servers['dmca']['port'] = 5337;
    $servers['dmca']['servername'] = 'TerraGov Marine Corps';
	//$servers['terry']['errortext'] = "dmca";
	$servers['dmca']['errortext'] = "Server not online";
	//$servers['dmca']['errortext'] = "Downloading more ram";
	$servers['dmca']['popcap'] = 180;
	
	$servers['ftl13'] = Array();
    $servers['ftl13']['address'] = 'eurobeat.tgstation13.org';
    $servers['ftl13']['port'] = 42069;
    $servers['ftl13']['servername'] = 'Alt Codebase Server 2';
	//$servers['terry']['errortext'] = "dmca";
	$servers['ftl13']['errortext'] = "Things seem tilted";
	$servers['ftl13']['popcap'] = 180;
	//$servers['ftl13']['eventcolors'] = true;
	
	$servers['campbell'] = Array();
    $servers['campbell']['address'] = 'campbell.tgstation13.org';
    $servers['campbell']['port'] = 6337;
    $servers['campbell']['servername'] = 'Campbell [Roleplay/EU]';
	$servers['campbell']['errortext'] = "Down for maintenance";
	//$servers['campbell']['popcap'] = 50;
	//$servers['campbell']['eventcolors'] = true;

?>