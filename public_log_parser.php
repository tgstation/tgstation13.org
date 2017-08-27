<?php
//This has very little documentation, edit the server array around line 175 to your server(s) 
//	symlink server-gamedata/servername to the static folder of tgs3 (gamedata folder for tgs2), and symlink parsed-logs to a folder accessable by the webserver
//	you also need the runtime condenser in the folder rc, with the binary named rc.
//	it creates .gz files, you can abuse http-gzip-static and a few rewrite rules in nginx to make nginx serve them up as gzip compressed text files.

if (php_sapi_name() != "cli")
	exit;

proc_nice(19);
function getfoldersinfolder($folder) {
	$results = scandir($folder);
	$folders = array();
	foreach ($results as $result) {
		if (!is_dir($folder . '/' . $result)) 
			continue;
		if ($result === '.' or $result === '..')
			continue;

		$folders[] = $folder . '/' . $result;	
	}
	return $folders;
}

function getfilesinfolder($folder) {
	$results = scandir($folder);
	$files = array();
	foreach ($results as $result) {
		if (!is_file($folder . '/' . $result)) 
			continue;
		if ($result === '.' or $result === '..')
			continue;

		$files[] = $folder . '/' . $result;
	}
	return $files;
}

function getroundsbyserver($server) {
	$years = getfoldersinfolder('server-gamedata/'.$server.'/data/logs');
	
	$logfiles = array();
	foreach ($years as $year) {

		if (!is_numeric(basename($year)))
			continue;
		
		$months = getfoldersinfolder($year);

		foreach ($months as $month) {
			if (!is_numeric(basename($month)))
				continue;
			$days = getfoldersinfolder($month);
			
			foreach ($days as $day) {
				$rounds = getfoldersinfolder($day);
				foreach ($rounds as $round) {
					
					$roundid = (int) substr(basename($round), 6);
					//echo "$round => $roundid\n";
					if ($roundid < 2)
						continue;
					
					$logfiles[] = $round;
				}
			}
		}
	}
	return $logfiles;
}
function compressfile($file, $target = null) {
	if ($target) {
		echo "compressing file:$file to $target\n";
		exec('gzip -kcf9 "'.$file.'" > "'.$target.'.gz"');
	} else {
		echo "compressing file:$file\n";
		exec('gzip -f9 "'.$file.'"');
	}
}
function parseruntime($runtime, $newpath) {
	echo "parsing runtime: $runtime -> $newpath/runtime.log\n";
	
	$file = fopen($runtime, 'rb');
	if ($file === false)
		return;
	$newfile = gzopen($newpath.'/runtime.txt.gz', 'wb9');
	if ($newfile === false) {
		fclose($file);
		return;
	}
	$condensefile = fopen('rc/Input.txt', 'wb');

	//parse each line
	while (($line = fgets($file)) !== false) {
		//Remove ips
		$line = preg_replace('/(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])/', '-censored-', $line);
		//Remove byond printed strings
		$line = preg_replace('/.*Cannot read \".*/', '-censored (string output)', $line);

		//write it to the public location
		gzwrite($newfile, $line);
		//write it to the runtime condenser
		fwrite($condensefile, $line);
	}
	fclose($file);
	gzclose($newfile);
	fclose($condensefile);
	echo "Condensing runtime: $runtime\n";
	chdir('rc');
	exec('./rc</dev/null');
	chdir('..');
	compressfile('rc/Output.txt', $newpath.'/runtime.condensed.txt');

	//compressfile($newfilename.'-condensed.txt');
	echo "Parse finished on runtime: $runtime\n";
}

function updateconfig($server) {
	$serverfiles = array('config/game_options.txt', 'config/maps.txt', 'config/unbuyableshuttles.txt', 'config/lavaruinblacklist.txt', 'config/spaceruinblacklist.txt', 'config/awaymissionconfig.txt');
	$serverfolders = array('data/minimaps', 'data/npc_saves');
	$filteredfiles = array('config/config.txt');
	$sharedfiles = array('admins.txt', 'admin_ranks.txt');
	if (!file_exists('parsed-logs/'.$server))
			mkdir('parsed-logs/'.$server, 0775, true);
	foreach ($serverfolders as $serverfolder) {
		if (!file_exists('server-gamedata/'.$server.'/'.$serverfolder))
			continue;
		foreach (getfilesinfolder('server-gamedata/'.$server.'/'.$serverfolder) as $file) {
			$target = 'parsed-logs/'.$server.'/'.$serverfolder.'/'.basename($file);
			if (!file_exists(dirname($target)))
				mkdir(dirname($target), 0775, true);
			//@copy($file, $target);
			@compressfile($file, $target);
		}
	}
	foreach ($serverfiles as $serverfile) {
		if (!file_exists('server-gamedata/'.$server.'/'.$serverfile))
			continue;
		$target = 'parsed-logs/'.$server.'/'.$serverfile;
		if (!file_exists(dirname($target)))
			mkdir(dirname($target), 0775, true);
		compressfile('server-gamedata/'.$server.'/'.$serverfile, $target);
		//compressfile('parsed-logs/'.$server.'/config/'.$serverfile);
	}
	foreach ($sharedfiles as $serverfile) {
		if (!file_exists('server-gamedata/shared/'.$serverfile))
			continue;
		$target = 'parsed-logs/'.$server.'/config/'.$serverfile;
		if (!file_exists(dirname($target)))
			mkdir(dirname($target), 0775, true);
		compressfile('server-gamedata/shared/'.$serverfile, $target);
		//compressfile('parsed-logs/'.$server.'/config/'.$serverfile);
	}
	foreach ($filteredfiles as $serverfile) {
		filterconfig($server, $serverfile);
	}
		
}
function filterconfig($server, $configfile) {
	echo "filtering $configfile";
	$filteredconfigs = array('COMMS_KEY', 'MEDAL_HUB_PASSWORD');
	$file = fopen('server-gamedata/'.$server.'/'.$configfile, 'rb');
	if ($file === false)
		return;
	$newfile = gzopen('parsed-logs/'.$server.'/'.$configfile.'.gz', 'wb9');
	if ($newfile === false) {
		fclose($file);
		return;
	}
	while (($line = fgets($file)) !== false) {
		foreach ($filteredconfigs as $filter)
			if (strstr(strtoupper($line), strtoupper($filter)) !== false)
				$line = '#'.$filter.' -FILTERED-'."\n";

		gzwrite($newfile, $line);
	}
	echo "done filtering $configfile";
}
echo "Starting up\n";
$servers = array('sybil', 'basil');
foreach ($servers as $server) {
	echo "Loading $server\n";
	updateconfig($server);
	//parseruntimes($server);
	//continue;
	echo "Parsing logs files\n";
	$basenewpath = 'parsed-logs/'.$server.'/data/logs';
	$first = true;
	$last = false;
	$years = array_reverse(getfoldersinfolder('server-gamedata/'.$server.'/data/logs'));
	
	foreach ($years as $year) {
		$baseyear = basename($year);
		if (!is_numeric($baseyear))
			continue;
		
		$months = array_reverse(getfoldersinfolder($year));
		foreach ($months as $month) {
			$basemonth = basename($month);
			$wrotemonth = false;
			if (!is_numeric($basemonth))
				continue;
			if (!file_exists("$basenewpath/$baseyear"))
				mkdir("$basenewpath/$baseyear",0775,true);
			$monthzip = new PharData("$basenewpath/$baseyear/month.$baseyear-$basemonth.zip", Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, null, Phar::ZIP);
			$monthzip->startBuffering();
			$days = array_reverse(getfoldersinfolder($month));
			foreach ($days as $day) {
				$baseday = basename($day);
				$wroteday = false;
				if (!file_exists("$basenewpath/$baseyear/$basemonth"))
					mkdir("$basenewpath/$baseyear/$basemonth",0775,true);
				$dayzip = new PharData("$basenewpath/$baseyear/$basemonth/day.$baseyear-$basemonth-$baseday.zip", Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, null, Phar::ZIP);
				$dayzip->startBuffering();
				$rounds = array_reverse(getfoldersinfolder($day));
				foreach ($rounds as $round) {
					$baseround = basename($round);
					$roundid = (int) substr($baseround, 6);
					//echo "$round => $roundid\n";
					if ($roundid < 2)
						continue;

					if ($first) {
						$first = false;
						continue;
					}

					$roundA = explode('/', $round);
					$roundA[0] = 'parsed-logs';
					$newpath = implode('/', $roundA);
					
					if (!file_exists($newpath)) {
						mkdir($newpath,0775,true);
					} else {
						$dayzip->stopBuffering();
						$monthzip->stopBuffering();
						break 4;
					}
					$roundzip = new PharData($newpath.'.zip', Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, null, Phar::ZIP);
					$roundzip->startBuffering();
					
					$logfiles = getfilesinfolder($round);
					foreach ($logfiles as $logfile) {
						$basename = basename($logfile);
						switch ($basename) { //DON'T JUDGE ME OK.
							case 'game.log':
								parsegamelog($logfile, $newpath, TRUE, TRUE);
								$handle = gzopen($newpath.'/game.txt.gz', 'r');
								$monthzip[$baseday.'/'.$baseround.'/game.txt'] = $handle;
								$monthzip[$baseday.'/'.$baseround.'/game.txt']->compress(Phar::BZ2);
								
								$handle = gzopen($newpath.'/game.txt.gz', 'r'); //writing a handle consumes it, so we have to re-open this =\
								$dayzip[$baseround.'/game.txt'] = $handle;
								$dayzip[$baseround.'/game.txt']->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/game.txt.gz', 'r');
								$roundzip['game.txt'] = $handle;
								$roundzip['game.txt']->compress(Phar::BZ2);
								
								gzclose($handle); //as far as I can tell its already closed at this point but this doesn't error out.
								break;
							case 'runtime.log':
								parseruntime($logfile, $newpath);
								$handle = gzopen($newpath.'/runtime.txt.gz', 'r');
								$monthzip[$baseday.'/'.$baseround.'/runtime.txt'] = $handle;
								$monthzip[$baseday.'/'.$baseround.'/runtime.txt']->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/runtime.txt.gz', 'r');
								$dayzip[$baseround.'/runtime.txt'] = $handle;
								$dayzip[$baseround.'/runtime.txt']->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/runtime.txt.gz', 'r');
								$roundzip['runtime.txt'] = $handle;
								$roundzip['runtime.txt']->compress(Phar::BZ2);
								
								gzclose($handle);
								break;
								
							case 'sql.log':
								parsegamelog($logfile, $newpath, FALSE, FALSE);
								$handle = gzopen($newpath.'/sql.txt.gz', 'r');
								$monthzip[$baseday.'/'.$baseround.'/sql.txt'] = $handle;
								$monthzip[$baseday.'/'.$baseround.'/sql.txt']->compress(Phar::BZ2);
								
								$handle = gzopen($newpath.'/sql.txt.gz', 'r'); //writing a handle consumes it, so we have to re-open this =\
								$dayzip[$baseround.'/sql.txt'] = $handle;
								$dayzip[$baseround.'/sql.txt']->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/sql.txt.gz', 'r');
								$roundzip['sql.txt'] = $handle;
								$roundzip['sql.txt']->compress(Phar::BZ2);
								break;
								
							case 'attack.log':
							case 'qdel.log':
							case 'initialize.log':
								compressfile($logfile, $newpath.'/'.basename($basename, ".log").'.txt');
								
								$handle = gzopen($newpath.'/'.basename($basename, ".log").'.txt.gz', 'r');
								$monthzip[$baseday.'/'.$baseround.'/'.basename($basename, ".log").'.txt'] = $handle;
								$monthzip[$baseday.'/'.$baseround.'/'.basename($basename, ".log").'.txt']->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/'.basename($basename, ".log").'.txt.gz', 'r');
								$dayzip[$baseround.'/'.basename($basename, ".log").'.txt'] = $handle;
								$dayzip[$baseround.'/'.basename($basename, ".log").'.txt']->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/'.basename($basename, ".log").'.txt.gz', 'r');
								$roundzip[basename($basename, ".log").'.txt'] = $handle;
								$roundzip[basename($basename, ".log").'.txt']->compress(Phar::BZ2);
								
								gzclose($handle);
								break;
							case 'kudzu.html':
							case 'wires.html':
							case 'atmos.html':
							case 'cargo.html':
							case 'gravity.html':
							case 'records.html':
							case 'singulo.html':
							case 'experimentor.html':
							case 'supermatter.html':
							case 'botany.html':
							case 'telesci.html':
								compressfile($logfile, $newpath.'/'.$basename);
								$handle = gzopen($newpath.'/'.$basename.'.gz', 'r');
								$monthzip[$baseday.'/'.$baseround.'/'.$basename] = $handle;
								$monthzip[$baseday.'/'.$baseround.'/'.$basename]->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/'.$basename.'.gz', 'r');
								$dayzip[$baseround.'/'.$basename] = $handle;
								$dayzip[$baseround.'/'.$basename]->compress(Phar::BZ2);
								$handle = gzopen($newpath.'/'.$basename.'.gz', 'r');
								$roundzip[$basename] = $handle;
								$roundzip[$basename]->compress(Phar::BZ2);
								
								gzclose($handle);
								break;
							case 'config_error.log':
							case 'hrefs.html':
								break;
							default:
								echo "(default) $logfile => $newpath/$basename\n";
								break;
						}
					}
					$roundzip->stopBuffering();
				}
				$dayzip->stopBuffering();
			}
			$monthzip->stopBuffering();
		}
	}
}
function parselog($logfile, $newpath, $lineparse, $htmlify) {
	echo "Parsing logfile: $logfile\n";

	$filename = basename($logfile);

	echo "Parsing logfile as Game Log: $logfile\n";
	
	$file = fopen($logfile, "rb");
	if ($file === false)
		return;
	$tofile = gzopen($newpath.'/game.txt.gz', "wb9");
	if ($tofile === false) {
		@fclose($file);
		return;
	}
	if (!is_resource($file) || !is_resource($tofile)) {
		@fclose($file);
		@fclose($tofile);
		return;
	}
	
	$tofilehtml = NULL;
	if ($htmlify) {
		$tofilehtml = gzopen($newpath.'/game.html.gz', "wb9");
		if ($tofilehtml === false || !is_resource($tofilehtml)) {
			@fclose($file);
			@fclose($tofile);
			return;
		}
		gzwrite($tofilehtml, '<html><head><title>Log file: '.$newpath.' - /tg/station 13</title><link rel="stylesheet" type="text/css" href="/logfilestyle.css"></head><body>');
	}
	
	while (($line = fgets($file)) !== false) {
		$rawline = trim($line, "\n\r");
		$parsedline = parseline($rawline, htmlspecialchars($line));
		$html = "";
		if ($htmlify)
			$html = preg_replace('/(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])/', '<span class="censored">-censored(ip)-</span>', $parsedline[1]);
		
		$line = preg_replace('/(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])/', '-censored(ip)-', $parsedline[0]); //remove ips

		gzwrite($tofile, $line."\r\n");
		if ($htmlify)
			gzwrite($tofilehtml, $html);
	}
	if ($htmlify) {
		gzwrite($tofilehtml, '</body></html>');
		gzclose($tofilehtml);
	}
	fclose($file);
	gzclose($tofile);

}
	
	
function parseline ($line, $html) {
	$line = trim($line);
	if (!$line)
		return array('-censored(misc)-','<p class="censored">-censored(misc)-</p>');
	if ($line[0] != '[')
		return array('-censored(misc)-','<p class="censored">-censored(misc)-</p>');
	$words = explode(' ', $line);
	$htmlwords = explode(' ', $html);
	
	$logtype = (explode(']',$words[0])[1]);
	switch ($logtype) {
		case 'ACCESS:':
			if ($words[1] == 'Login:') {
				$words[count($words)-4] = '-censored(ip/cid)-';
				$htmlwords[count($words)-4] = '<span class="censored">-censored(ip/cid)-</span>';
			}
			if ($words[1] == 'Failed')
				return '-censored(invalid connection data)-';
			break;
		case 'ADMIN:':
			if ($words[1] == 'HELP:')
				return censor('asay/apm/ahelp/notes/etc');
			if ($words[1] == 'PM:')
				return censor('asay/apm/ahelp/notes/etc');
			if ($words[1] == 'ASAY:')
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) : /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) added note /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) removed a note /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) has added /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) has edited /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			/*if (preg_match('/ADMIN: .*\\/\\(.*\\) has created a /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) has removed a /', $line))
				return censor('asay/apm/ahelp/notes/etc');
			if (preg_match('/ADMIN: .*\\/\\(.*\\) has deleted a /', $line))
				return censor('asay/apm/ahelp/notes/etc');*/ //old messages about message/note/memo adding.
			if ($words[1] == '<a')
				return censor('asay/apm/ahelp/notes/etc');
			break;
		case 'ADMINPRIVATE:':
			return censor('private logtype');
			break;
		case 'ASAY:':
			return censor('asay/apm/ahelp/notes/etc');
			break;
		case 'SQL:':
			return censor('sql logs');
			break;
		case 'SAY:':
		case 'WHISPER:':
		case 'OOC:':
			foreach ($htmlwords as $i=>$word) {
				if ($word == ':') {
					$htmlwords[1] = '<b>'.$htmlwords[1];
					$htmlwords[$i-1] = $htmlwords[$i-1].'</b>';
					break;
				}
			}
			break;
		default:
			break;
	}		
	
	return array(implode(' ',$words), '<p class="'.rtrim(explode(']',$words[0])[1],':').'">'.implode(' ',$htmlwords).'</p>');
}

function censor($reason) {
	return array('-censored('.$reason.')-','<p class="censored">-censored('.$reason.')-</p>');
}
	
	
	
	
	
	
	
	
	

?>
