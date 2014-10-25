<?php 
require_once("include/include.php");

$user = auth();

$tpl = new template("index");
	
$thm = new theme("Home");

$thm->send($tpl);
?>