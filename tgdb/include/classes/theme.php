<?php

//requires: include.php/template.php

class theme {
	private $pagetitle;
	public function theme($pagetitle = "") {
		$this->pagetitle = $pagetitle;
	}
	public function title($pagetitle) {
		$this->pagetitle = $pagetitle;
	}
	public function send($tpl) {
		$content = "";
		if ($tpl instanceof template)
			$content = $tpl->process();
		else
			$content = $tpl;
		
		$maintheme = new template("theme", array(
			"CONTENT"		=>	$content,
			"HEADER"		=>	$this->header(),
			"FOOTER"		=>	$this->footer(),
			"PAGE_TITLE"	=>	$this->fmtpagetitle()
		));
		echo $maintheme->process();
	}
	private function header() {
		$navbar = navbar::get();
		return (new template("header", array('NAVBAR' => $navbar)))->process();
	}
	private function footer() {
		return (new template("footer"))->process();
	}
	
	private function fmtpagetitle() {
		return "User Manager: ".$this->pagetitle; //todo: make this use a sitename variable of some kind
	}
}
?>