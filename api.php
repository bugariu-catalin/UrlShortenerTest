<?php

require_once 'inc/config.inc.php';
require_once 'inc/shortener.inc.php';

$result = array();
if (isset($_REQUEST['url'])) {
	$api = new Shortener();
	$data = $api->getShortcode($_REQUEST['url']);
	$errors = $api->getErrors();
	if (!$errors) {
		$result['data'] = $data;
	} else {
		$result['error'] = 'There was an internal error. Please try again!';
	} 
	if ($errors && DEBUG) $result['error_debug'] = print_r($errors, true);
} else {
	$result['error'] = 'Missing url!';
}

echo json_encode($result);

?>