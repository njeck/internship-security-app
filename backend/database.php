<?php

function db_connect(): mysqli
{
	$configPath = dirname(__DIR__) . '/config.php';

	if (!file_exists($configPath)) {
		die('Missing config.php. Copy config.example.php to config.php and update your database settings.');
	}

	$config = require $configPath;
	$conn = new mysqli(
		$config['db_host'],
		$config['db_user'],
		$config['db_pass'],
		$config['db_name']
	);

	if ($conn->connect_error) {
		die('Connection failed: ' . $conn->connect_error);
	}

	return $conn;
}
