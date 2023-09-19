<?php
//This has very little documentation, and is a hobbled togeather mess where speed to code and preformance of the code were placed before code readability or maintainability. Only works on linux. Requires gzip and gunzip shell commands as well as the php extentions.

//	edit the server array below to your server(s) 
//	symlink server-gamedata/servername to the static folder of tgs3 (gamedata folder for tgs2), and symlink parsed-logs to a folder accessable by the webserver
//	you also need the runtime condenser in the folder rc, with the binary named rc
//	it creates .gz files, you can abuse http-gzip-static and a few rewrite rules in nginx to make nginx serve them up as http-gzip compressed text files.

$servers = array('sybil', 'basil');

if (php_sapi_name() != "cli")
	exit;
$stop = TRUE;
$overwrite = FALSE;
$configonly = FALSE;
$options = getopt("doc", array("diff", "overwrite", "configonly"));

if (isset($options["diff"]) || isset($options["d"]))
	$stop = FALSE;
if (isset($options["overwrite"]) || isset($options["o"]))
	$overwrite = TRUE;
if (isset($options["configonly"]) || isset($options["c"]))
	$configonly = TRUE;
/*
for ($argv as $argn=>$arg)
	if ($argn > 0)
		switch ($arg) {
			case "-d":
			case "--diff":
				$stop = FALSE;
				break;
			case "-o":
			case "--overwrite":
				$overwrite = TRUE;
				break;
		}
if ($argc >= 2 && $argv[1] === "-diff")
	$stop = FALSE;
*/
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
	usleep(100000);
}

function condense_runtimes($infile, $outfile) {
	echo "Condensing runtime: $infile -> $outfile\n";
	chdir('rc');
	exec('gunzip -kc ../"'.$infile.'" | ./rc -s | gzip -c9 > ../"'.$outfile.'"');
	chdir('..');
	usleep(100000);
}

function parseruntime($runtime, $newpath, $monthruntimes, $dayruntimes) {
	echo "parsing runtime: $runtime -> $newpath/runtime.log\n";

	$file = fopen($runtime, 'rb');
	if ($file === false)
		return;
	$newfile = gzopen($newpath.'/runtime.txt.gz', 'wb9');
	if ($newfile === false) {
		fclose($file);
		return;
	}

	//parse each line
	while (($line = fgets($file)) !== false) {
		//Remove ips
		$line = preg_replace('/(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])/', '-censored-', $line);
		//Remove byond printed strings
		$line = preg_replace('/.*Cannot read \".*/', '-censored (string output)', $line);

		//write it to the public locations
		gzwrite($newfile, $line);
		gzwrite($monthruntimes, $line);
		gzwrite($dayruntimes, $line);
	}
	fclose($file);
	gzclose($newfile);
	condense_runtimes($newpath.'/runtime.txt.gz', $newpath.'/runtime.condensed.txt.gz');

	//compressfile($newfilename.'-condensed.txt');
	echo "Parse finished on runtime: $runtime\n";
	usleep(100000);
}

function updateconfig($server) {
	$serverfiles = array('config/maps.txt', 'config/server_overrides.txt', 'data/paintings.json');
	$serverfolders = array('data/minimaps', 'data/npc_saves', 'data/engravings');
	$diffedfolders = array('data/Diagnostics/Resources');
	$filteredfiles = array('config.txt', 'game_options.txt', 'mrp_shared_overrides.txt');
	$sharedfiles = array('admins.txt', 'admin_nicknames.json', 'admin_nicknames.txt', 'admin_ranks.txt', 'awaymissionconfig.txt', 'dynamic.json', 'lavaruinblacklist.txt', 'motd.txt', 'mrp_motd.txt', 'policy.json', 'resources.txt', 'sillytips.txt', 'spaceruinblacklist.txt', 'tips.txt', 'unbuyableshuttles.txt', 'server_side_modifications.dm' => 'server_side_modifications.dm');
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
	
	foreach ($diffedfolders as $difffolder) {
		if (!file_exists('server-gamedata/'.$server.'/'.$difffolder))
			continue;
		$stop = false;
		foreach (array_reverse(getfilesinfolder('server-gamedata/'.$server.'/'.$difffolder)) as $file) {
			if (!ctype_digit(substr(basename($file), 0, 1)))
				continue;
			$target = 'parsed-logs/'.$server.'/'.$difffolder.'/'.basename($file);
			if (!file_exists(dirname($target)))
				mkdir(dirname($target), 0775, true);
			echo "$file -> $target\n";
			if (file_exists($target.'.gz'))
				$stop = true;
			//@copy($file, $target);
			@compressfile($file, $target);
			if ($stop)
				break;
		}
	}
	
	foreach ($sharedfiles as $source => $target) {
		if (is_numeric($source)) {
			$source = $target;
			$target = 'config/'.$target;
		}
		if (!file_exists('server-gamedata/shared/'.$source))
			continue;
		$target = 'parsed-logs/'.$server.'/'.$target;
		if (!file_exists(dirname($target)))
			mkdir(dirname($target), 0775, true);
		compressfile('server-gamedata/shared/'.$source, $target);
		//compressfile('parsed-logs/'.$server.'/config/'.$serverfile);
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
	
	foreach ($filteredfiles as $serverfile) {
		filterconfig($server, $serverfile);
	}
	
	//Paintings
	$painting_categories = getfoldersinfolder('server-gamedata/'.$server.'/data/paintings');
	foreach ($painting_categories as $category) {
		$target_dir = 'parsed-logs/'.$server.'/data/paintings/'.basename($category);
		if (!file_exists($target_dir))
			mkdir($target_dir, 0775, true);
		foreach (getfilesinfolder($category) as $file) {
			$target = $target_dir.'/'.basename($file);
			if (!file_exists($target.'.gz'))
				@compressfile($file, $target);
		}
	}
		
}
function filterconfig($server, $configfile) {
	echo "filtering $configfile\n";
	$filteredconfigs = array('COMMS_KEY', 'MEDAL_HUB_PASSWORD', 'DISCORD_TOKEN');
	$file = fopen('server-gamedata/shared/'.$configfile, 'rb');
	if ($file === false)
		return;
	$newfile = gzopen('parsed-logs/'.$server.'/config/'.$configfile.'.gz', 'wb9');
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
	echo "done filtering $configfile\n";
	usleep(100000);
}

function fillzips($file, $basename, $monthzip, $dayzip, $roundzip, $day, $round) {
	return;
	$handle = gzopen($file, 'r');
	
	$monthzip[$day.'/'.$round.'/'.$basename] = $handle;
	$monthzip[$day.'/'.$round.'/'.$basename]->compress(Phar::BZ2);
	
	gzrewind($handle);
	
	$dayzip[$round.'/'.$basename] = $handle;
	$dayzip[$round.'/'.$basename]->compress(Phar::BZ2);
	
	gzrewind($handle);
	
	$roundzip[$basename] = $handle;
	$roundzip[$basename]->compress(Phar::BZ2);

	gzclose($handle);
	usleep(100000);
}

function fill_round_zip($file, $basename, &$roundzip, $day, $round) {
	if (!($roundzip instanceof PharData)) {
		$roundzip = new PharData($roundzip, Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, null, Phar::ZIP);
		$roundzip->startBuffering();
	}
	$handle = gzopen($file, 'r');
	
	$roundzip[$basename] = $handle;
	$roundzip[$basename]->compress(Phar::BZ2);

	gzclose($handle);
	usleep(100000);
}

echo "Starting up\n";
$servers = array('sybil', 'basil', 'terry', 'event-hall', 'manuel', 'event-hall-us');
foreach ($servers as $server) {
	echo "Loading $server\n";
	updateconfig($server);
	copy_pictures($server);
	if ($configonly)
		continue;
	
	//parseruntimes($server);
	//continue;
	echo "Parsing logs files\n";
	$basenewpath = 'parsed-logs/'.$server.'/data/logs';
	$first = true;
	$last = false;
	$years = array_reverse(getfoldersinfolder('server-gamedata/'.$server.'/data/logs'));
	$dozips = false;
	
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
			if (!file_exists("$basenewpath/$baseyear/$basemonth"))
				mkdir("$basenewpath/$baseyear/$basemonth", 0775, true);
			if ($dozips) {
				$monthzip = new PharData("$basenewpath/$baseyear/month.$baseyear-$basemonth.zip", Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, null, Phar::ZIP);
				$monthzip->startBuffering();
			}
			if ($stop)
				$monthruntimes = gzopen("$basenewpath/$baseyear/$basemonth/$baseyear-$basemonth.runtime.txt.gz", "ab9");
			$days = array_reverse(getfoldersinfolder($month));
			foreach ($days as $day) {
				$baseday = basename($day);
				$wroteday = false;
				if (!file_exists("$basenewpath/$baseyear/$basemonth/$baseday"))
					mkdir("$basenewpath/$baseyear/$basemonth/$baseday", 0775, true);
				if ($dozips) {
					$dayzip = new PharData("$basenewpath/$baseyear/$basemonth/day.$baseyear-$basemonth-$baseday.zip", Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME, null, Phar::ZIP);
					$dayzip->startBuffering();
				}
				if ($stop)
					$dayruntimes = gzopen("$basenewpath/$baseyear/$basemonth/$baseday/$baseyear-$basemonth-$baseday.runtime.txt.gz", "ab9");
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
					} else if ($stop) {
						if ($dozips) {
							$dayzip->stopBuffering();
							$monthzip->stopBuffering();
						}
						gzclose($monthruntimes);
						condense_runtimes("$basenewpath/$baseyear/$basemonth/$baseyear-$basemonth.runtime.txt.gz", "$basenewpath/$baseyear/$basemonth/$baseyear-$basemonth.runtime.condensed.txt.gz");
						gzclose($dayruntimes);
						condense_runtimes("$basenewpath/$baseyear/$basemonth/$baseday/$baseyear-$basemonth-$baseday.runtime.txt.gz", "$basenewpath/$baseyear/$basemonth/$baseday/$baseyear-$basemonth-$baseday.runtime.condensed.txt.gz");

						break 4;
					}
					$roundzip = $newpath.'.zip';
					
					$logfiles = getfilesinfolder($round);
					foreach ($logfiles as $logfile) {
						$basename = basename($logfile);
						switch ($basename) {
							case 'game.log':
								$basefilename = basename($basename, '.log');
								$fullnewpath = $newpath.'/'.$basefilename.'.txt';
								
								if (!$overwrite && file_exists($fullnewpath.'.gz'))
									continue;
								
								parselog($logfile, $newpath, TRUE, TRUE);
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basefilename.'.txt', $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basefilename.'.txt', $roundzip, $baseday, $baseround);
								
								$fullnewpath = $newpath.'/'.$basefilename.'.html';
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basefilename.'.html', $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basefilename.'.html', $roundzip, $baseday, $baseround);
								break;
							
							case 'runtime.log':
								$basefilename = basename($basename, '.log');
								$fullnewpath = $newpath.'/'.$basefilename.'.txt';
								
								if (!$overwrite && file_exists($fullnewpath.'.gz'))
									continue;
								
								parseruntime($logfile, $newpath, $monthruntimes, $dayruntimes);
								
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basefilename.'.txt', $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basefilename.'.txt', $roundzip, $baseday, $baseround);
								
								$fullnewpath = $newpath.'/'.$basefilename.'.condensed.txt';
								
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basefilename.'.condensed.txt', $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basefilename.'.condensed.txt', $roundzip, $baseday, $baseround);
								
								break;
							
							case 'sql.log':
								$basefilename = basename($basename, '.log');
								$fullnewpath = $newpath.'/'.$basefilename.'.txt';
								
								if (!$overwrite && file_exists($fullnewpath.'.gz'))
									continue;
								
								parselog($logfile, $newpath, FALSE, FALSE);
								
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basefilename.'.txt', $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basefilename.'.txt', $roundzip, $baseday, $baseround);
								
								break;
							case 'attack.log':
							case 'dynamic.log':
							case 'qdel.log':
							case 'initialize.log':
							case 'pda.log':
							case 'telecomms.log':
							case 'transport.log':
							case 'overlay.log':
							case 'manifest.log':
							case 'job_debug.log':
							case 'virus.log':
							case 'econ.log':
							case 'signals.log':
							case 'shuttle.log':
							case 'mecha.log':
							case 'asset.log':
							case 'map_errors.log':
							case 'cloning.log':
							case 'uplink.log':
							case 'paper.log':
							case 'harddel.log':
							case 'speech_indicators.log':
								$basefilename = basename($basename, '.log');
								$fullnewpath = $newpath.'/'.$basefilename.'.txt';
								
								if (!$overwrite && file_exists($fullnewpath.'.gz'))
									continue;
								
								compressfile($logfile, $fullnewpath);
								
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basefilename.'.txt', $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basefilename.'.txt', $roundzip, $baseday, $baseround);
									
								break;
							
							case 'kudzu.html':
							case 'wires.html':
							case 'atmos.html':
							case 'cargo.html':
							case 'deaths.html':
							case 'gravity.html':
							case 'records.html':
							case 'singulo.html':
							case 'experimentor.html':
							case 'supermatter.html':
							case 'engine.html':
							case 'botany.html':
							case 'presents.html':
							case 'telesci.html':
							case 'research.html':
							case 'radiation.html':
							case 'portals.html':
							case 'hallucinations.html':
							case 'hypertorus.html':
							case 'circuit.html':	
							case 'nanites.html':
							case 'newscaster.json':
							case 'dynamic.json':
							case 'round_end_data.json':
							case 'round_end_data.html':
							case 'profiler.json':
							case 'sendmaps.json':
							case 'id_card_changes.html':
							case 'target_zone_switch.json':
							case 'silo.json':
							case ((substr($basename, 0, 5) == 'perf-') ? $basename : !$basename):
								$fullnewpath = $newpath.'/'.$basename;
								
								if (!$overwrite && file_exists($fullnewpath.'.gz'))
									continue;
								
								compressfile($logfile, $fullnewpath);
								
								if ($dozips)
									fillzips($fullnewpath.'.gz', $basename, $monthzip, $dayzip, $roundzip, $baseday, $baseround);
								else
									fill_round_zip($fullnewpath.'.gz', $basename, $roundzip, $baseday, $baseround);
								break;
							
							case 'config_error.log':
							case 'hrefs.html':
							case 'hrefs.log':
								break;
							default:
								echo "!!(default) $logfile\n";
								break;
						}
					}
					$logfolders = getfoldersinfolder($round);
					foreach ($logfolders as $logfolder) {
						$basename = basename($logfolder);
						switch ($basename) {
							case 'photos':
								if (!file_exists($newpath.'/photos'))
										mkdir($newpath.'/photos',0775,true);
								foreach (getfilesinfolder($logfolder) as $picturefile) {
									$basename = basename($picturefile);
									$fullnewpath = $newpath.'/photos/'.$basename;
									
									copy($picturefile, $fullnewpath);
									
									if ($dozips)
										fillzips($fullnewpath, 'photos/'.$basename, $monthzip, $dayzip, $roundzip, $baseday, $baseround);
									else
										fill_round_zip($fullnewpath, 'photos/'.$basename, $roundzip, $baseday, $baseround);
								}
							break;
							default:
								echo "(folder default) $logfile => $newpath/$basename\n";
								break;
						}
						
					}
					if ($roundzip instanceof PharData)
						$roundzip->stopBuffering();
				}
				if ($dozips)
					$dayzip->stopBuffering();
				if ($stop) {
					gzclose($dayruntimes);
					condense_runtimes("$basenewpath/$baseyear/$basemonth/$baseday/$baseyear-$basemonth-$baseday.runtime.txt.gz", "$basenewpath/$baseyear/$basemonth/$baseday/$baseyear-$basemonth-$baseday.runtime.condensed.txt.gz");
				}
			}
			if ($dozips)
				$monthzip->stopBuffering();
			if ($stop) {
				gzclose($monthruntimes);
				condense_runtimes("$basenewpath/$baseyear/$basemonth/$baseyear-$basemonth.runtime.txt.gz", "$basenewpath/$baseyear/$basemonth/$baseyear-$basemonth.runtime.condensed.txt.gz");
			}
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
	$tofile = gzopen($newpath.'/'.basename($filename, '.log').'.txt.gz', "wb9");
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
		$parsedline = $rawline;
		if ($lineparse)
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
	usleep(100000);
}
	
	
function parseline ($line, $html) {
	$line = trim($line);
	if (!$line)
		return array('-censored(misc)-','<p class="censored">-censored(misc)-</p>');
	if ($line[0] != '[')
		return array('-censored(misc)-','<p class="censored">-censored(misc)-</p>');
	
	$words = explode(' ', $line);
	$htmlwords = explode(' ', $html);
	
	$logtype = explode(']',$words[0]);
	
	if (count($logtype) < 2) {
		$logtype = $words[2];
		$words[0] .= ' '.$words[1].' '.$words[2];
		$htmlwords[0] .= ' '.$htmlwords[1].' '.$htmlwords[2];
		array_splice($words, 1, 2);
		//echo "$words[0]|||$words[1]|||$words[2]|||$logtype\n";
	} else {
		$logtype = $logtype[1];
	}
	switch ($logtype) {
		case 'ACCESS:':
			if ($words[1] == 'Login:') {
				$words[count($words)-4] = '-censored(ip/cid)-';
				$htmlwords[count($htmlwords)-4] = '<span class="censored">-censored(ip/cid)-</span>';
			}
			if ($words[1] == 'Failed')
				return censor('invalid connection data');
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
			if (preg_match('/ADMIN: [^:]*\\/\\(.*\\) ".*"/', $line))
				return censor('asay/apm/ahelp');
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

function copy_pictures($server) {
	global $stop, $overwrite;
	echo "Parsing picture log files\n";
	$basenewpath = 'parsed-logs/'.$server.'/data/picture_logs';
	$first = true;
	$last = false;
	$years = array_reverse(getfoldersinfolder('server-gamedata/'.$server.'/data/picture_logs'));
	$dozips = false;
	
	foreach ($years as $year) {
		$baseyear = basename($year);
		if (!is_numeric($baseyear))
			continue;
		
		$months = array_reverse(getfoldersinfolder($year));
		foreach ($months as $month) {
			$basemonth = basename($month);
			if (!is_numeric($basemonth))
				continue;
			if (!file_exists("$basenewpath/$baseyear/$basemonth"))
				mkdir("$basenewpath/$baseyear/$basemonth", 0775, true);
			
			$days = array_reverse(getfoldersinfolder($month));
			foreach ($days as $day) {
				$baseday = basename($day);
				if (!file_exists("$basenewpath/$baseyear/$basemonth/$baseday"))
					mkdir("$basenewpath/$baseyear/$basemonth/$baseday", 0775, true);
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
					} else if ($stop) {
						break 4;
					}
					
					$pictures = getfilesinfolder($round);
					foreach ($pictures as $picture) {
						$basename = basename($picture);
						$fullnewpath = $newpath.'/'.$basename;
						if (!$overwrite && file_exists($fullnewpath))
							continue;
						copy($picture, $newpath.'/'.$basename);
					}
				}
			}
		}
	}
}

?>
