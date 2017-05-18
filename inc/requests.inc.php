<?php

# Проверка обновлений
define( 'VERSION', getCurrentVersion() );

function getCurrentVersion() {
	$ver = '?';
	$path = 'inc/version.txt';
	if ( file_exists($path) ) {
		$f = trim( file_get_contents($path) );
		if ($f) {
			$ver = $f;
		}
	}
	return $ver;
}

function checkUpdates() {
	$res = false;
	$url  = 'https://api.github.com/repos/prog-it/Asterisk-CDR-Viewer-Mod/releases/latest';
	if ( VERSION != '?' ) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko');
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	
		$content = curl_exec($ch);
		$hc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$err = curl_errno($ch);
		$errmsg = curl_error($ch);
		curl_close($ch);	
		if ( !$err && !$errmsg && in_array($hc, array(200)) ) {
			if ($content) {
				$res = json_decode($content);
			}
		}
	}
	return $res;
}

if ( isset($_POST['check_updates']) ) {
	if ( strlen($cdr_user_name) > 0 ) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	$upd = checkUpdates();
	if ( $upd !== false ) {
		$msg = 'Нет обновлений';
		if ( $upd->name > VERSION ) {
			$msg = 'Доступна новая версия: ' . $upd->name . PHP_EOL .
					'Текущая версия: ' . VERSION . PHP_EOL . PHP_EOL .
					'В этом релизе:' . PHP_EOL .
					$upd->body;
		}
		echo json_encode(array(
			'success' => true,
			'message' => $msg,
		));
	} else {
		echo json_encode(array(
			'success' => false,
			'message' => 'Не удалось проверить обновления',
		));		
	}
	exit;
}

# Удаление записи звонка
if ( isset($_POST['delete_record']) ) {
	if ( strlen($cdr_user_name) > 0 || $display_search['rec_delete'] == 0 ) {
		header('HTTP/1.1 403 Forbidden');
		exit;
	}
	$path = $system_monitor_dir . '/' . base64_decode($_POST['delete_record']);
	if ( file_exists($path) && is_file($path) ) {
		if ( @unlink($path) ) {
			echo json_encode(array(
				'success' => true,
				'message' => 'Успешно удалено',
			));			
		} else {
			echo json_encode(array(
				'success' => false,
				'message' => 'Нет прав на папку с файлом',
			));			
		}
	} else {
		echo json_encode(array(
			'success' => false,
			'message' => 'Файл не существует',
		));			
	}
	exit;
}
	
	
	
	
	
