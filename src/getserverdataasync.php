<?php
if (php_sapi_name() != "cli")
	return;

include("serverinfo.php");

$serverdataarray = array();
$socketarray = array();
$serversocketarray = array();

$str = "?status&format=json";

/* --- Prepare a packet to send to the server (based on a reverse-engineered packet structure) --- */
$query = "\x00\x83" . pack('n', strlen($str) + 6) . "\x00\x00\x00\x00\x00" . $str . "\x00";

/* --- Create sockets for each of the servers and start them connecting. --- */
foreach ($servers as $servername => $server) {
	$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP) or exit("ERROR");
	
	socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>2, 'usec'=>0));
	socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec'=>2, 'usec'=>0));
	
	socket_set_nonblock($socket);
	
	$socketserverarray[$socket] = $servername;
	$socketarray[] = $socket;
	socket_connect($socket, $server['address'], $server['port']);
}

//$socketarray = array_keys($socketserverarray);

//print_r($socketarray);

$r = array();
$e = array();
$w = $e = $socketarray;
usleep(2000000);
$numchanged = socket_select($r, $w, $e, 1); //wait 2 seconds for our connecting sockets to connect
echo "connect numchanged: $numchanged\n";


foreach ($w as $socket)
	socket_write($socket,$query);

foreach (array_diff($socketarray, $w) as $socket)
	$serverdataarray[$socketserverarray[$socket]] = 'ERROR: connection error';

$r = $w;
$w = array();
usleep(2000000);
$numchanged = socket_select($r, $w, $e, 0);
echo "read numchanged: $numchanged\n";
foreach ($r as $socket) {
	$result = socket_read($socket, 10000, PHP_BINARY_READ);
	@socket_close($socket);

	if($result != "") {
		if($result{0} == "\x00" || $result{1} == "\x83") { // make sure it's the right packet format
			
			// Actually begin reading the output:
			$sizebytes = unpack('n', $result{2} . $result{3}); // array size of the type identifier and content
			$size = $sizebytes[1] - 1; // size of the string/floating-point (minus the size of the identifier byte)
			
			if($result{4} == "\x2a") { // 4-byte big-endian floating-point
				$unpackint = unpack('f', $result{5} . $result{6} . $result{7} . $result{8}); // 4 possible bytes: add them up together, unpack them as a floating-point
				$serverdataarray[$socketserverarray[$socket]] = 'ERROR: Unexpected int: '.$unpackint;
				continue;
			}
			else if($result{4} == "\x06") { // ASCII string
				$unpackstr = ""; // result string
				$index = 5; // string index
				
				while($size > 0) { // loop through the entire ASCII string
					$size--;
					$unpackstr .= $result{$index}; // add the string position to return string
					$index++;
				}
				$serverdataarray[$socketserverarray[$socket]] = $unpackstr;
				continue;
			}
		}
	}	
	$serverdataarray[$socketserverarray[$socket]] = 'ERROR: read error';
}

foreach (array_diff($socketarray, $r) as $socket)
	$serverdataarray[$socketserverarray[$socket]] = 'ERROR: read timeout';


$file = 'serverinfo.json';
$cache = array();

if (file_exists($file)) {
	$cache = file_get_contents($file);
	$cache = @json_decode($cache, true);
}

$serverinfo = array('refreshtime' => 6007);

foreach ($servers as $servername => $server) {
	
	
	$lastinfo = ((is_array($cache) && array_key_exists($servername, $cache)) ? $cache[$servername] : array());
	//$n++;
	$data = $serverdataarray[$servername];
	if(is_string($data)) {
		//remove pesky null-terminating bytes
		$data = str_replace("\x00", '', $data); 
	}
	$variable_value_array = array();
	$variable_value_array['serverdata'] = $server;
	$variable_value_array['cachetime'] = time();
	if ((!$data || strpos($data, 'ERROR') !== false)) {
		$variable_value_array['error'] = true;
		$variable_value_array['errormsg'] = $data;
		if (array_key_exists('restarting', $lastinfo))
			$variable_value_array['restarting'] = $lastinfo['restarting'] + 1;
		else if (array_key_exists('cachetime', $lastinfo) && !array_key_exists('error', $lastinfo)) {
			if (time() - $lastinfo['cachetime'] <= 6) {
				$variable_value_array = $lastinfo;
				$variable_value_array['errorgrace'] = 1;
			}
		}
		print "Error on $servername: $data\n";
	} else {
		$variable_value_array = array_merge(json_decode($data, TRUE), $variable_value_array);
	}


	

	if (array_key_exists('gamestate', $variable_value_array)) 
		if ((int)$variable_value_array['gamestate'] == 4)
			$variable_value_array['restarting'] = 1;

	$serverinfo[$servername] = $variable_value_array;
}

file_put_contents($file, json_encode($serverinfo));
foreach ($socketarray as $socket) {
	$linger     = array ('l_linger' => 0, 'l_onoff' => 1);
    @socket_set_option ($socket, SOL_SOCKET, SO_LINGER, $linger);
	@socket_set_block($socket);
	@socket_close($socket);
	
}
?>