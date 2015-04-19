<?php

require_once 'inc/config.inc.php';
require_once 'inc/Vendor/Test/Shortener.php';
use Vendor\Test;

if (isset($_REQUEST['shortcode'])) {
	$api = new Vendor\Test\Shortener();
	if (!$api->redirectToUrl($_REQUEST['shortcode'])) {
		header("HTTP/1.0 404 Not Found");
		include '404.php';
	}
} else {
	header("Location: ./");
}
