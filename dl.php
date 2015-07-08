<?php

require_once 'inc/config.inc.php';
require_once 'inc/sendfile.class.php';

use SendFile\sendFile as sendFile;

if (isset($_REQUEST['f'])) {
	$fname = base64_decode($_REQUEST['f']);
	$file = $system_monitor_dir . '/' . $fname;
	$send = new sendFile;
	$send->Path = $file;
	$send->send();
	exit;
}

else if (isset($_REQUEST['csv'])) {
	$fname = base64_decode($_REQUEST['csv']);
	$file = $system_tmp_dir . '/' . $fname;
	$send = new sendFile;
	$send->Path = $file;
	$send->ContentType = 'text/csv';
	$send->send();	
	exit;
}

header('HTTP/1.1 404 Not Found');

