<?php //<--- you see this shit right here, that php tag, it better be the first thing in the file
		//If there is even a space or a newline before that tag, shit will break

//SETUP

define("DATA_STALE_TIME" ,600); //How many seconds before we consider the data stale and report an error

//END SETUP

$server_index = 0+$_GET['servernum'] - 1 ;	//Set this to the index that contains information about this server in the $servers array in serverinfo.php
//<MrStonedOne> set this with gamebanner.php?servernum=num when you request the image
//must be a number, (thats what the 0+ enforces)
//this will be used to generate a filename so if we allow non-numbers exploits and rootkits happen.

$debug = array_key_exists('debug', $_GET);
//This code written by doohl and errorage. Works with tgstation v2013.10.26.
//This code was made functional by Jeb
//This code was defucked and improved by MrStoneOne
//Check http://tgstation13.org to see if a newer version is available. bug someone on coderbus for it
//	will be added to the tgstation github once finalized.


//to prevent error stacks from showing; just show the error message in the generated image instead
ini_set('display_errors', 0); 

//sets $addr, $port, $servername
include("serverinfo.php");

$error = false; //if set to true, will update the cache, but sets cachetime to CACHE_EXPIRE_TIME-1

// Connection settings: (overrides serverinfoinclude.php stuff)
$port = $servers[$server_index]["port"];
$servername = $servers[$server_index]["servername"];
$addr = $servers[$server_index]["address"];
$errortext = "Connection Error!";
$errortext_override = false;
if (isset($servers[$server_index]["errortext"])) {
	$errortext = $servers[$server_index]["errortext"];
	$errortext_override = true;
}


//Settings for what gets displayed and what doesn't are lower down in the file, in the makeImage() function.

function roundtofive($count) {
	return $count;
	if ($count == 0) return 0;
	if ($count < 15) return $count;
	if ($count % 5 == 0) return $count;
	return $count + (5-($count % 5));
}

function debugmsg($message) {
global $debug;
	if ($debug) {
		print_r ($message);
		echo "\n";
	}
}

//fill the image with a background based on predefined color palettes
//todo: move the color palettes to a config, store the whole rgb as one var and split it here

function fillbg($image, $color) {
	$bgcolor = array();
		switch ($color) {
			case "green":	
				$bgcolor[] = imagecolorallocate ($image, 0x26, 0x8c, 0x2f);
				$bgcolor[] = imagecolorallocate ($image, 0x7f, 0xba, 0x85);
				$bgcolor[] = imagecolorallocate ($image, 0xab, 0xd0, 0xb0);
				$bgcolor[] = imagecolorallocate ($image, 0xd8, 0xe7, 0xdc);
				break;
			case "blue":
				$bgcolor[] = imagecolorallocate ($image, 0x26, 0x29, 0x8c);
				$bgcolor[] = imagecolorallocate ($image, 0x82, 0x82, 0xbb);
				$bgcolor[] = imagecolorallocate ($image, 0xb0, 0xae, 0xd2);
				$bgcolor[] = imagecolorallocate ($image, 0xde, 0xdb, 0xea);
				break;
			
			case "yellow":
				$bgcolor[] = imagecolorallocate ($image, 0x8c, 0x88, 0x26);
				$bgcolor[] = imagecolorallocate ($image, 0xbb, 0xba, 0x82);
				$bgcolor[] = imagecolorallocate ($image, 0xd2, 0xd3, 0xb0);
				$bgcolor[] = imagecolorallocate ($image, 0xe9, 0xeb, 0xde);
				break;
			default: //default to red
				$bgcolor[] = imagecolorallocate ($image, 0x8c, 0x26, 0x2c);
				$bgcolor[] = imagecolorallocate ($image, 0xba, 0x81, 0x83);
				$bgcolor[] = imagecolorallocate ($image, 0xd1, 0xae, 0xae);
				$bgcolor[] = imagecolorallocate ($image, 0xe9, 0xdd, 0xdb);
				break;
		}
		
	//fill the image with the last color in the array
	imagefill($image, 0, 0, $bgcolor[count($bgcolor)-1]);
	
	//loop thru the colors, making a 1px border from each of them
	// with each next border inside of the previous one (to make a 3d border like effect)
	for ($i=0; $i < count($bgcolor); $i++) 
		imagerectangle($image, $i, $i, imagesx($image)-1-$i, imagesy($image)-1-$i, $bgcolor[$i]);
	
	//return the first color from the array for use as the text color
	return $bgcolor[0];
}

function getvar($array,$var) {
	if (array_key_exists($var, $array))
		return $array[$var];
	return null;
}

function validvar($var){
	if(!isset($var)){
		return false;
	}
	if(!$var && $var != 0){
		return false;
	}
	
	return true;
}

function imageerror ($image, $errormsg) {
	global $error,$addr,$port,$servername;
	if ($image == null)
		$image = imagecreatetruecolor(232, 72);
	$textColorURL = imagecolorallocate ($image, 0xa9, 0xa6, 0xcc);
	$textColor = fillbg($image, "red");
	//Print url and port
	$string = "$addr:$port";
	imagestring ($image, 2, 5, 0,  $string, $textColorURL);
	
	//Print fail message
	$string = $servername.$errormsg;
	imagestring ($image, 5, 5, 16,  $servername, $textColor);
	imagestring ($image, 5, 5, 32,  $errormsg, $textColor);
	$error = true;
	return $image;
}

//not used much, calculates where to place text dynamically based on how many lines of each size you've already done.
//here at /tg/station13 hardcoding is our middle name. =(
function getTop($fontsize, $heightnorm, $heightbig, $printedsmall, $printedbig) {
	return $printedsmall*$heightnorm + $printedbig*$heightbig + imagefontheight($fontsize);
}
function secondsToTime($seconds) {
	$output = '';
	if ($seconds >= 86400)
		$output .= sprintf("%02d", floor($seconds / 86400)).':';
	
	if ($seconds >= 3600)
		$output .= sprintf("%02d", floor($seconds / 3600) % 24) . ':';
	
	$output .= sprintf("%02d", floor(($seconds / 60) % 60)) . ':' . sprintf("%02d", $seconds % 60);
	return $output;
}

function shuttleTime($shuttlemode, $shuttletime) {
	
	switch($shuttlemode){
		case 'igniting':
			return 'IGN '.secondsToTime($shuttletime);
		case 'recall':
			return 'RCL '.secondsToTime($shuttletime);
		case 'call':
			return 'ETA '.secondsToTime($shuttletime);
		case 'docked':
			return 'ETD '.secondsToTime($shuttletime);
		case 'escape':
			return 'ESC '.secondsToTime($shuttletime);
		case 'stranded':
			return 'ERR --:--';
		case 'endgame: game over':
			return 'FIN 00:00';
	}
	return '';
	
}


//This function converts the data into an actual image.
function makeImage($variable_value_array) {
	global $error,$addr,$port,$servername,$server_index,$errortext,$errortext_override;
	$image = imagecreatetruecolor(232, 72);
	$textColor = imagecolorallocate ($image, 0x26, 0x29, 0x8c);
	$textColorURL = imagecolorallocate ($image, 0xa9, 0xa6, 0xcc);
	//first things first, lets check for bad shit.
	if (!$variable_value_array || !is_array($variable_value_array) || count($variable_value_array) == 0) {
		return imageerror($image, "No Data!");
	}
	
	if (array_key_exists("ERROR", $variable_value_array)) {
		if (($restarting = getvar($variable_value_array,"restarting")) && $restarting < 18 && $errortext_override === false) {
			return imageerror($image, "Server Restarting");
		}
		return imageerror($image, "$errortext");
	}
	
	$fontsmaller = 2;
	$fontsmall = 4;
	$fontbig = 5;
	$heightnorm = imagefontheight($fontsmall) ;
	$heightbig = imagefontheight($fontbig) ;

	

	//Settings for what we want to print.
	$printplayers = 1;
	$printmode = 1;
	$printurl = 1;
	$printadmins = 0;
	$printhost = 0;
	$printrevision = 1;
	$printversion = 1;
	$printmode = 1;
	$printmap = 1;

	//Move variables from the array into easier to use variables
	//^^^ WHY???? todo: remove all of this and make shit use the array -mrstonedone
	
	$version = getvar($variable_value_array,"version");
	$mode = getvar($variable_value_array,"mode");
	$respawn = getvar($variable_value_array,"respawn");
	$entering = getvar($variable_value_array,"enter");
	$voting = getvar($variable_value_array,"vote");
	$ai = getvar($variable_value_array,"ai");
	$host = getvar($variable_value_array,"host");
	$players = getvar($variable_value_array,"players");
	$active_players = getvar($variable_value_array,"active_players");
	$revision = getvar($variable_value_array,"revision");
	$revision_date = getvar($variable_value_array,"revision_date");
	$admins = getvar($variable_value_array,"admins");
	$active_players = getvar($variable_value_array,"active_players");
	$map_name = getvar($variable_value_array,"map_name");
	$gamestate = getvar($variable_value_array,"gamestate");

#define GAME_STATE_PREGAME		1
#define GAME_STATE_SETTING_UP	2
#define GAME_STATE_PLAYING		3
#define GAME_STATE_FINISHED		4

	//Create an image object
	$image = imagecreatetruecolor( 232,72 );
	
	//Allocate colors and remember them for use when writing strings
	//If you want to change these, simply change the numbers. They are
	//in hexadecimal: red, green and blue. If you go find a html code for
	//a color (like #ffcc00), you can create a new object with that color
	//with: 
	// $textColorGold = imagecolorallocate ($image, 0xff, 0xcc, 0x00);
	//Then go through the calls to imagestring(...) below that and change
	//the ones you want to use that color ($textColorGold). Ignore the
	//imagestring(...) calls that create the simple background when changing
	//color, those really don't matter.
	
	$textColor = imagecolorallocate ($image, 0x26, 0x29, 0x8c);
	$textColorReserve = imagecolorallocate ($image, 0xff, 0xff, 0xff);
	$textColorRed = imagecolorallocate ($image, 0xff, 0x00, 0x00);
	$textColorURL = imagecolorallocate ($image, 0xa9, 0xa6, 0xcc);

	//old background code, replaced by fillbg()
/* 	if(file_exists("bg.png")){
		//Create the image from the background file if it exists
		if ($gamestate == 3){
		$image = imagecreatefrompng ( "bg.png" );}
	}else{
		//Create a simple background. Doesn't work well in some resolutions.
		$textColor = $textColorReserve;
		imagestring ($image, 5, 310, 10,  "XXX XXX X XXX", $textColor);
		imagestring ($image, 5, 310, 22,  "X   X   X   X", $textColor);
		imagestring ($image, 5, 310, 34,  "XXX XXX X XXX", $textColor);
		imagestring ($image, 5, 310, 46,  "  X   X X   X", $textColor);
		imagestring ($image, 5, 310, 58,  "XXX XXX X XXX", $textColor);
		imagestring ($image, 1, 335, 82,  " X XXX XXX  X", $textColor);
		imagestring ($image, 1, 335, 90,  " X  X  X    X", $textColor);
		imagestring ($image, 1, 335, 98,  "X   X  X X X ", $textColor);
		imagestring ($image, 1, 335, 106, "X   X  XXX X ", $textColor);
		
	} */

	//These are used to determine the y position from the top for the next
	//line of text to be written. If you hardcode all the coordinates, these
	//aren't necessary, but if you want to only display certain lines when
	//data is available or if data fits certain criteria (like if there are
	//more than 0 admins online), you might want to use these variables and
	//the getTop() procedure. Remember to include a printedsmall++ or printedbig++
	//wherever you print non-hardcoded lines.
	//Increment $printedsmall when you use $fontsmall
	//Increment $printedbig when you use $fontbig

	$printedsmaller = 0;
	$printedsmall = 0;
	$printedbig = 0;

	if (!validvar($addr) || !validvar($port)) {
		return imageerror($image, "Invalid Config!");
	}
	
	if (!validvar($players) || !validvar($mode)) {
		$restarting = getvar($variable_value_array,"restarting");
		if ($restarting && $restarting < 18) {
			return imageerror($image, "Server Restarting");
		}

		return imageerror($image, "$errortext");
	}
	$bgcolor = "blue";
	
	//1 == lobby 2 == setting up the game 3 == playing 4 == shuttle docked at centcom
	
	if ($gamestate == 1)
		$bgcolor = "green";
	if ($gamestate == 4)
		$bgcolor = "red";
	
	$textColor = fillbg($image, $bgcolor);
	//Print server url and version (hint: It's actually the revision date, shh!)
	if ($printurl && $revision_date) {
		//if we have a revision date, parse it and print
		$revision_date_f = "v" . str_replace("-", ".", $revision_date);
		$string = "$addr:$port $revision_date_f";
		imagestring ($image, 2, 4, 0,  $string, $textColorURL);
	} else if ($printurl) {
		//If we don't have a revision date, but want to print the url, print it.
		$string = "$addr:$port";
		if ($printrevision && validvar($revision))
			$string .= " ".substr($revision,0,10);
		imagestring ($image, 2, 4, 0,  $string, $textColorURL);
	}
	
	//If we have a server name, print it.
	if (validvar($servername)) {
		$string = "$servername";
		imagestring ($image, $fontbig, 4, 13,  $string, $textColor);
	}
	
	//Lets properly print the players shall we.
	if ($printplayers) {
		$string = '';
		if ($server_index < 2)
			$string = $players."/90";
		else
			$string = $players." online";
		$offset = 0;
		$cachetime = getvar($variable_value_array, "cachetime");
		if (validvar($cachetime))
			$offset = time() - $cachetime;
		$roundTime = getvar($variable_value_array, "round_duration");
		if (validvar($roundTime))
			$string .= ' '.secondsToTime($roundTime+$offset);
		$shuttleMode = getvar($variable_value_array, "shuttle_mode");
		$shuttleTimer = getvar($variable_value_array, "shuttle_timer");
		if (validvar($shuttleMode) && validvar($shuttleTimer))
			$string .= ' '.shuttleTime($shuttleMode, $shuttleTimer - $offset);
		imagestring ($image, $fontsmall, 4, 55,  $string, $textColor);
		$printedsmall++;
	}

	//Guess we can also rip off erro some more, mimic modo go!
	if ($printmode) {
		// I'm too lazy to escape shit in the image code, so version is hardcoded here
		$string = "playing " . urldecode($version) . " mode \"" . $mode . "\"";
		imagestring ($image, $fontsmaller, 4, 28, $string, $textColor);
		$printedsmaller++;
	}

	//Someone set us up the map
	if ($printmap) {
		$string = "the map is: $map_name";
		imagestring ($image, $fontsmaller, 4, 42, $string, $textColor);
		$printedsmall++;
	}

	
	//Print the revision (works terribly with git, but perfectly with svn)
	/*
	if($printrevision && validvar($revision)) {
		$string = substr($revision,0,7);
		imagestring ($image, 2, 11, getTop($fontsmall, $heightnorm,$heightbig, $printedsmall, $printedbig),  $string, $textColor);
		$printedsmall++;
	}
	*/
	
	
	
	//Generates the png and destroy the object we were using
	//imagepng($image, "".$saveloc);
	//imagedestroy($image);
	return $image;
}


//load data the cron job was suppose to set
$file = "serverinfo.json";
$data = array();
if (file_exists($file)) {
	$handle = fopen($file, "r");
	$cache = fread($handle, filesize($file));
	fclose($handle);
	$cache = @json_decode($cache, true);//@ sign prevents errors from stopping the code
	//if it fails, we want to still get the info by hand
	
	if ($cache && is_array($cache) && count($cache) >= $server_index)
		$data = $cache[$server_index];
	
}
debugmsg($cache);
debugmsg($data);

if (is_array($data) && array_key_exists('cachetime',$data) && $data['cachetime'] > time() - DATA_STALE_TIME) 
	$variable_value_array = $data;
else 
	$variable_value_array = null;

debugmsg($variable_value_array);


/* Finally, make the image and print it to the browser */
$outputimage = makeImage($variable_value_array);

/* Redirect to the actual output file itself */
//header('Location: ./' . $saveloc); // Voila!

if (!$debug) {
	header('Content-Type: image/png');
	imagepng($outputimage, null, 9, PNG_ALL_FILTERS); //if you have issues with high cpu usage, lower this.
}
imagedestroy($outputimage);

//if the data didn't come from cache, lets store it

/* === Returned packet format: ===

Useful information:
$variable_value_array[%key%] = %value%:

%key%				%value%
"version"			codebase name (servers based on our code read "/tg/Station13". Hardcoded in game code)
"mode"				game mode name (example: "secret")
"respawn"			respawn enabled (1/0)
"enter"				entering enabled (1/0)
"vote"				voding allowed (1/0)
"ai"				ai allowed (1/0)
"host"				key of game host (usually "Guest-#" or something for /tg/ station. Set in the game's config files)
"players"			number of connected clients (example: "51", note it's not an actual integer)
"active_players"	number of connected clients who joined the game (example: "48", note it's not an actual integer)
"revision"			the hash code that identifies a revision on git ("aiowjt348az4niago34")
"revision_date"		the date the revision was merged onto git ("2013-10-29")
"admins"			number of admins online (example: "4", note it's not an actual integer)
*/

 ///\/ you see this shit right here, that closing php tag, it better be the last thing in the file
		//If there is even a space or a newline after that tag, shit will break
?>
