<?php

$path_config = 'inc/config.inc.php';

# Пользовательский конфиг
if ( isset($_REQUEST['config']) ) {
    if ( preg_match('#^[A-Za-z0-9]+$#', $_REQUEST['config']) && file_exists('inc/config-' . $_REQUEST['config'] . '.inc.php') ) {
        $path_config = 'inc/config-' . $_REQUEST['config'] . '.inc.php';
    }
}

require_once $path_config;
require_once 'inc/sendfile.class.php';

# Доступ запрещен
if ( strlen($cdr_user_name) > 0 ) {
	header('HTTP/1.1 403 Forbidden');
	exit;
}

if ( isset($_REQUEST['f']) ) {
	$fname = base64_decode($_REQUEST['f']);
	$file = $system_monitor_dir . '/' . $fname;
	$send = new Sendfile;
	$send->send($file);
	exit;
}

else if ( isset($_REQUEST['csv']) ) {
	$fname = base64_decode($_REQUEST['csv']);
	$file = $system_tmp_dir . '/' . $fname;
	$send = new Sendfile;
	$send->contentType('text/csv');
	$send->send($file);	
	exit;
}

header('HTTP/1.1 403 Not Found');

