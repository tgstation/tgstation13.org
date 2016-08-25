<?php
namespace tgdb;
require_once("include/include.php");

$tpl = new template("login");

echo $tpl->process();
?>