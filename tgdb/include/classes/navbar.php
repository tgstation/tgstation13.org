<?php
namespace tgdb;
class navbar {
	
	//format:
	//0=url
	//1=friendly name (defaults to everything between the last / and last . if omitted)
	//2=internal name (defaults to friendly name if omitted) used for toggling the active class on a navitem
	static private $items = array(
		array("index.php", "Home"),
		array("bans.php", "Ban DB", "ban"),
		array("notes.php", "Note DB", "note"),
		array("conndb.php", "Connection DB", "cdb"),
		array("player.php", "Player Lookup", "player"),
	);
	static private $active = null;

	static public function setactive($name) {
		foreach (self::$items as $i=>$item) {
			if (!$item)
				continue;
			$item = self::processitem($item);
			if (trim(strtolower($item[2])) == trim(strtolower($name))) {
				self::$active = $i;
				return;
			}
		}
	}
	
	//fills the item values that were left blank
	static private function processitem (array $item) {
		if (!$item)
			throw new exception("Processitem called with null argument");
		if (count($item) < 1)
			throw new exception("Processitem called on empty array");
		if (count($item) < 2) {
			$url = $item[0];
			$start = (strrpos($url, "/") ? strrpos($url, "/") : 0);
			if (strlen($url) <= $start -1) {
				$url = substr($url,0,strlen($url - 1));
				$start = (strrpos($url, "/") ? strrpos($url, "/") : 0);
			}
			$end = (strrpos($url, ".") ? strrpos($url, ".") : strlen($url)-1);
			if ($end <= $start)
				$end = strlen($url)-1;
			
			$item[1] = substr($url,$start,$start - $end);
		}
		if (count($item) < 3)
			$item[2] = $item[1];
		
		return $item;
	}
	
	static public function get () {
		$tpl = new template('navitem');
		
		$navbars = "";
		foreach (self::$items as $i=>$item) {
			if (!$item)
				continue;
			$item = self::processitem($item);
			$tpl->resetvars(array(
				"URL"		=>	$item[0],
				"NAME"		=>	$item[1],
				"ACTIVE"	=>	($i == self::$active ? "active" : "")
			));
			$navbars .= "\n".$tpl->process();
		}
		
		$tpl = new template('navbar', array("NAVBARITEMS" => $navbars));
		return $tpl->process();
		
	}
	

}

?>