<?php

function check_vite_connection()
{
	if (!isset($_ENV["IS_DEVELOPMENT"]) || !$_ENV["IS_DEVELOPMENT"]) {
		return false;
	}

	$host = "host.docker.internal";
	$port = 3000;
	$connection = @fsockopen($host, $port, $errno, $errstr, 5); // 5秒のタイムアウト

	if ($connection) {
		fclose($connection);
		return true;
	} else {
		return false;
	}
}
