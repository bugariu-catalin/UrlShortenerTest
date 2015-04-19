<?php

require_once 'inc/config.inc.php';
require_once 'inc/shortener.inc.php';

if (isset($_REQUEST['shortcode'])) {
	$api = new Shortener();
	if (!$api->redirectToUrl($_REQUEST['shortcode'])) {
		header("HTTP/1.0 404 Not Found");
		include '404.php';
	}
} else {
	header("Location: ./");
}
?>