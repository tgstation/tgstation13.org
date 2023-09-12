<?php
namespace tgdb;
//tracks how long things took.
//used to build the time table in footer.tpl

class timing {

	static private $times = array(); //name => array(0=>time, 1=>count)

	//mainly so template stuff still counts time while we are generating the timing table.
	//these will be processed in the order listed here
	static private $processlast = array(
	"tokenize()",
	"preprocess()",
	"process()",
	);

	static function gettime() {
		return (microtime(true)); //mainly so we can change how time tracking works in one place if ever needed.
	}
	
	static function tracktime($name, $time, $metatrack = false) {
		
		$t = self::gettime();
		
		$time = $t - $time;
		if ($time < 0)
			$time = 0;
		
		if (!isset(self::$times[$name]))
			self::$times[$name] = array(0.0, 0);
		
		self::$times[$name][0] += $time;
		self::$times[$name][1]++;
		//if ($metatrack)
			//self::tracktime($name." tracktime()", $t);
	}

	static function generatetimetable() {
		global $phptotaltime;
		$timetablerows = '';
		$tpl = new template('timetablerow');
		foreach (self::$times as $name => $data) {
			if (in_array($name, self::$processlast))
				continue; //we will process this in the foreach loop below
			
			$tpl->resetvars(array(
			'NAME'		=>	$name,
			'TIME'		=>	$data[0],
			'COUNT'		=>	$data[1]
			));
			$timetablerows .= $tpl->process();
		}
		
		foreach (self::$processlast as $name) {
			if (!isset(self::$times[$name]))
				continue;
				
			$data = self::$times[$name];
			$tpl->resetvars(array(
			'NAME'		=>	$name,
			'TIME'		=>	$data[0],
			'COUNT'		=>	$data[1]
			));
			$timetablerows .= $tpl->process();
		}
		$tpl->resetvars(array(
			'NAME'		=>	"PHP Total",
			'TIME'		=>	(microtime(true) - $phptotaltime),
			'COUNT'		=>	1
			));
		$timetablerows .= $tpl->process();
		
		$tpl->resetvars(array(
			'NAME'		=>	"PHP Memory",
			'TIME'		=>	(memory_get_peak_usage(true)/1024/1024)." MiB",
			'COUNT'		=>	1
			));
		$timetablerows .= $tpl->process();
		return (new template('timetable', array('TIMETABLEROWS' => $timetablerows)));
	}











}