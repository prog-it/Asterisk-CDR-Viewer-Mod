<?php

# Текущая версия
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

define( 'VERSION', getCurrentVersion() );

# Проверка обновлений
function checkUpdates() {
	$res = false;
	$url = 'https://github.com/prog-it/Asterisk-CDR-Viewer-Mod/raw/master/inc/version.txt';
	if ( VERSION != '?' ) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
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
			$content = trim($content);
			if ($content) {
				if ( $content > VERSION ) {
					$res = 'Доступна новая версия: ' . $content . PHP_EOL . 'Текущая версия: ' . VERSION;
				} else {
					$res = 'Нет обновлений';
				}
			}
		}
	}
	return $res;
}

if ( isset($_POST['check_updates']) ) {
	$upd = checkUpdates();
	if ( $upd !== false ) {
		echo json_encode(array(
			'success' => true,
			'message' => $upd,
		));
	} else {
		echo json_encode(array(
			'success' => false,
			'message' => 'Не удалось проверить обновления',
		));		
	}
	exit;
}
