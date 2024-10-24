<?php

require_once __DIR__ . '/src/schema.php';
require_once __DIR__ . '/src/registrations.php';


if(preg_match('/\/registrations\/(.*)/', $_SERVER['REQUEST_URI']) > 0) {
	header('HTTP/1.1 404 Not Found');
	$_GET['e'] = 404;
	exit;
}