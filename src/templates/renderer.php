<?php

function render($template, array $props = [])
{
	// Render template into string
	extract($props);
	ob_start();
	require __BASE__ . '/templates/' . $template . ".php";
	$output = ob_get_contents();
	ob_end_clean();
	return preg_replace('/[\s\n][\s\n]+/', '', $output);
}

if (!defined("__BASE__")) die("Access denied");
