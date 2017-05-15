<?php

/* Recorded file */
function formatFiles($row) {
	global
	$system_monitor_dir,
	$system_audio_format,
	$system_archive_format,
	$system_fsize_exists,
	$system_column_name,
	$system_storage_format,
	$system_audio_defconv,
	$display_search;

	# uniq_name.mp3
	$recorded_file = '';
	$tmp['system_audio_format'] = $system_audio_format;
	# В базе есть колонка с именем записи разговора
	if ( isset($row[$system_column_name]) ) {
		$recorded_file = $row[$system_column_name];
	}
	
	$mycalldate_ymd		= substr($row['calldate'], 0, 10); // ymd
	$mycalldate_ym		= substr($row['calldate'], 0, 7); // ym
	$mycalldate_y		= substr($row['calldate'], 0, 4); // y
	$mycalldate_m		= substr($row['calldate'], 5, 2); // m
	$mycalldate_d		= substr($row['calldate'], 8, 2); // d
	$mydate				= date('Y-m-d');

	// -----------------------------------------------
	
	# Кнопка прослушать запись
	$tpl['btn_record'] = '
		<div class="img_play" data-link="dl.php?f=[_file]" data-title="'.$row['calldate'].'"></div>
	';
	
	# Кнопка скачать запись
	$tpl['btn_download'] = '
		<a href="dl.php?f=[_file]"><div class="img_dl"></div></a>
	';	
	
	# Кнопка удалить запись
	$tpl['btn_delete'] = '
		<div data-path="[_file]" class="img_delete"></div>
	';
	
	# Файл не найден
	$tpl['error'] = '
		<td class="record_col">
			<div class="img_notfound"></div>
		</td>
	';

	# Прослушивание, скачивание, удаление
	$tpl['record'] = '
		<td class="record_col">
			<div class="recordBox">
				[_btn_record]
				[_btn_download]
				[_btn_delete]
			</div>
		</td>
	';
	
	# Скачивание, удаление
	$tpl['download'] = '
		<td class="record_col">
			<div class="recordBox">
				[_btn_download]
			</div>
		</td>
	';
	
	$tpl['record'] = str_replace(
		array(
			'[_btn_record]',
			'[_btn_download]',
			'[_btn_delete]',
		),
		array(
			$tpl['btn_record'],
			$tpl['btn_download'],
			$display_search['rec_delete'] == 1 ? $tpl['btn_delete'] : '',
		),
		$tpl['record']
	);
	$tpl['download'] = str_replace('[_btn_download]', $tpl['btn_download'], $tpl['download']);
					
	// -----------------------------------------------

	# Имя файла при отложенной конвертации
	if ( $system_audio_defconv === true && $recorded_file ) {
		if ( $mycalldate_ymd < $mydate ) {
			$recorded_file = preg_replace('#(.+)\.(wav|mp3|wma|ogg|aac)$#i', '${1}.'.$tmp['system_audio_format'], $recorded_file);
		} else {
			$tmp['system_audio_format'] = 'wav';
		}
	}	
	
	# Получение имени файла и пути
	if ( $mycalldate_ymd < $mydate && $system_storage_format === 1 ) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_ym/$mycalldate_ymd/$recorded_file";
	} else if ( $mycalldate_ymd < $mydate && $system_storage_format === 2 ) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_m/$mycalldate_d/$recorded_file";
	} else if ( $system_storage_format === 3 ) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_ym/$mycalldate_ymd/$recorded_file";
	} else if ( $system_storage_format === 4 ) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_m/$mycalldate_d/$recorded_file";
	} else {
		$rec['filename'] = $recorded_file;
	}
	
	$rec['path'] = $system_monitor_dir.'/'.$rec['filename'];
	
	# Аудио
	if ( file_exists($rec['path']) && $recorded_file && filesize($rec['path'])/1024 >= $system_fsize_exists && preg_match('#(.+)\.'.$tmp['system_audio_format'].'$#i', $rec['filename']) ) {
		$tmp['result'] = str_replace('[_file]', base64_encode($rec['filename']), $tpl['record']);
	}
	# Архив
	else if ( isset($system_archive_format) && $recorded_file && file_exists($rec['path'].'.'.$system_archive_format) && filesize($rec['path'].'.'.$system_archive_format)/1024 >= $system_fsize_exists ) {
		$tmp['result'] = str_replace('[_file]', base64_encode($rec['filename'].'.'.$system_archive_format), $tpl['download']);
	}
	# Факс
	//else if (file_exists($rec['path']) && preg_match('#(.*)\.tiff?$#i', $rec['filename']) && $rec['filesize'] >= $system_fsize_exists) {
	else if ( file_exists($rec['path']) && $recorded_file && filesize($rec['path'])/1024 >= $system_fsize_exists ) {
		$tmp['result'] = str_replace('[_file]', base64_encode($rec['filename']), $tpl['download']);
	}
	
	else { 
		$tmp['result'] = $tpl['error']; 
	}

	echo $tmp['result'];
}


/* CDR Table Display Functions */
function formatCallDate($calldate, $uniqueid) {
	//$calldate = date('d.m.Y H:i:s', strtotime($calldate));
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="UID: '.$uniqueid.'">'.$calldate.'</abbr></td>' . PHP_EOL;
}

function formatChannel($channel) {
	global $display_full_channel;
	$chan['short'] = preg_replace('#(.*)\/[^\/]+$#', '$1', $channel);
	$chan['full'] = preg_replace('#(.*)-[^-]+$#', '$1', $channel);
	$chan['tooltip'] = 'Канал: '.$chan['full'];
	$chan['txt'] = $chan['short'];
	if ( isset($display_full_channel) && $display_full_channel == 1 ) {
		$chan['txt'] = $chan['full'];
	}
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="'.$chan['tooltip'].'">'.$chan['txt'].'</abbr></td>' . PHP_EOL;
}

function formatClid($clid) {
	$clid_only = explode(' <', $clid, 2);
	$clid = htmlspecialchars($clid_only[0]);
	echo '<td class="record_col">'.$clid.'</td>' . PHP_EOL;
}

function formatSrc($src, $clid) {
	global $rev_lookup_url, $rev_min_number_len ;
	if ( empty($src) ) {
		echo '<td class="record_col">Неизвестно</td>' . PHP_EOL;
	} else {
		$src = htmlspecialchars($src);
		$clid = htmlspecialchars($clid);
		$src_show = $src;
		if ( is_numeric($src) && strlen($src) >= $rev_min_number_len && strlen($rev_lookup_url) > 0 ) {
			$rev = str_replace('%n', $src, $rev_lookup_url);
			$src_show = '<a href="'.$rev.'" target="reverse">'.$src.'</a>';
		}
		echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="CallerID: '.$clid.'">'.$src_show.'</abbr></td>' . PHP_EOL;
	}
}

function formatApp($app, $lastdata) {
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="Приложение: '.$app.'('.$lastdata.')">'.$app.'</abbr></td>' . PHP_EOL;
}

function formatDst($dst, $dcontext) {
	global
	$rev_lookup_url,
	$rev_min_number_len;
	
	$dst_show = $dst;
	if ( is_numeric($dst) && strlen($dst) >= $rev_min_number_len && strlen($rev_lookup_url) > 0 ) {
		$rev = str_replace('%n', $dst, $rev_lookup_url);
		$dst_show = '<a href="'.$rev.'" target="reverse">'.$dst.'</a>';
	}
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="Контекст назначения: '.$dcontext.'">'.$dst_show.'</abbr></td>' . PHP_EOL;
}

function formatDisposition($disposition, $amaflags) {
	switch ($amaflags) {
		case 0:
			$amaflags = 'DOCUMENTATION';
			break;
		case 1:
			$amaflags = 'IGNORE';
			break;
		case 2:
			$amaflags = 'BILLING';
			break;
		case 3:
		default:
			$amaflags = 'DEFAULT';
	}
	// Стиль текста для вызовов
	$style = '';
	switch ($disposition) {
		case 'ANSWERED':
			$dispTxt = 'Отвечено';
			$style = 'answer';
			break;
		case 'NO ANSWER':
			$dispTxt = 'Не отвечено';
			$style = 'noanswer';
			break;
		case 'BUSY':
			$dispTxt = 'Занято';
			$style = 'busy';
			break;
		case 'FAILED':
			$dispTxt = 'Ошибка';
			$style = 'failed';
			break;
		case 'CONGESTION':
			$dispTxt = 'Перегрузка';
			$style = 'congestion';
			break;			
		default:
			$dispTxt = $disposition;
	}
	echo '<td class="record_col '.$style.'"><div class="status status-'.$style.'"></div><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="AMA флаг: '.$amaflags.'">'.$dispTxt.'</abbr></td>' . PHP_EOL;
}

function formatDuration($duration, $billsec) {
	$duration = sprintf( '%02d', intval($duration/60) ).':'.sprintf( '%02d', intval($duration%60) );
	$billduration = sprintf( '%02d', intval($billsec/60) ).':'.sprintf( '%02d', intval($billsec%60) );
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="По биллингу: '.$billduration.'">'.$duration.'</abbr></td>' . PHP_EOL;
}

function formatUserField($userfield) {
	echo '<td class="record_col">'.$userfield.'</td>' . PHP_EOL;
}

function formatAccountCode($accountcode) {
	echo '<td class="record_col">'.$accountcode.'</td>' . PHP_EOL;
}

/* Asterisk RegExp parser */
function asteriskregexp2sqllike($source_data, $user_num) {
	$number = $user_num;
	if (strlen($number) < 1) {
		$number = $_REQUEST[$source_data];
	}
	if ('__' == substr($number,0,2)) {
		$number = substr($number,1);
	} elseif ('_' == substr($number,0,1)) {
		$number_chars = preg_split('//', substr($number,1), -1, PREG_SPLIT_NO_EMPTY);
		$number = '';
		foreach ($number_chars as $chr) {
			if ($chr == 'X') {
				$number .= '[0-9]';
			} elseif ($chr == 'Z') {
				$number .= '[1-9]';
			} elseif ($chr == 'N') {
				$number .= '[2-9]';
			} elseif ($chr == '.') {
				$number .= '.+';
			} elseif ($chr == '!') {
				$_REQUEST[ $source_data .'_neg' ] = 'true';
			} else {
				$number .= $chr;
			}
		}
		$_REQUEST[$source_data .'_mod'] = 'asterisk-regexp';
	}
	return $number;
}

/* empty() wrapper */
function is_blank(&$value) {
	if ( isset($value) ) {
		return empty($value) && !is_numeric($value);
	}
	return true;
}

/* 
	Money format

*/
// cents: 0=never, 1=if needed, 2=always
// title: title to show
function formatMoney($number, $cents = 2, $title = '') {
	global $callrate_currency;
	if ( is_numeric($number) ) {
		// whole number
		if ( floor($number) == $number ) {
			$money = number_format( $number, ($cents == 2 ? 2 : 0) ); // format
		} else { // cents
			$money = number_format( round($number, 2), ($cents == 0 ? 0 : 2) ); // format
		} // integer or decimal
		
		if ( $title ) {
			$title = ' class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="'.$title.'"';
		}
		echo '<td class="chart_data"><span'.$title.'>'.$money.'</span>'.$callrate_currency.'</td>' . PHP_EOL;
	} else {
		echo '<td class="chart_data">&nbsp;</td>' . PHP_EOL;
	}
}

/* 
	CallRate
	return callrate array [ areacode, rate, description, bill type, total_rate] 
*/
function callrates($dst, $duration, $file) {
	global
	$callrate_csv_file,
	$callrate_cache,
	$callrate_free_interval;

	if (strlen($file) == 0) {
		$file = $callrate_csv_file;
		if (strlen($file) == 0) {
			return array('','','','','');
		}
	}
	
	if (!array_key_exists($file, $callrate_cache)) {
		$callrate_cache[$file] = array();
		$fr = fopen($file, 'r') or exit('Не удалось открыть файл с тарифами ('.$file.')');
		while (($fr_data = fgetcsv($fr, 1000, ',')) !== false) {
			if ($fr_data[0] !== null) {
				// Не указан доп. тариф
				if (!isset($fr_data[4])) {
					$fr_data[4] = null;
				}
				$callrate_cache[$file][$fr_data[0]] = array($fr_data[1], $fr_data[2], $fr_data[3], $fr_data[4]);
			}
		}
		fclose($fr);
	}

	for ($i = strlen($dst); $i > 0; $i--) {
		if (array_key_exists(substr($dst,0,$i), $callrate_cache[$file])) {
			$call_rate = 0;
			if ($callrate_cache[$file][substr($dst,0,$i)][2] == 's') {
				// per second
				if ($duration >= $callrate_free_interval) {
					$call_rate = $duration * ($callrate_cache[$file][substr($dst,0,$i)][0] / 60);
				}
			} elseif ($callrate_cache[$file][substr($dst,0,$i)][2] == 'c') {
				// per call
				if ($duration >= $callrate_free_interval) {
					$call_rate = $callrate_cache[$file][substr($dst,0,$i)][0];
				}
			} elseif ($callrate_cache[$file][substr($dst,0,$i)][2] == '1m+s') {
				// 1 minute + per second
				if ($duration < $callrate_free_interval) {}
				else if ( $duration < 60 ) {
					$call_rate = $callrate_cache[$file][substr($dst,0,$i)][0];
				} else {
					// указан доп тариф
					if (isset($callrate_cache[$file][substr($dst,0,$i)][3]) && $callrate_cache[$file][substr($dst,0,$i)][3] && $callrate_cache[$file][substr($dst,0,$i)][3] > 0) {
						$ext_rate = $callrate_cache[$file][substr($dst,0,$i)][3];
					} else {
						$ext_rate = $callrate_cache[$file][substr($dst,0,$i)][0]/60;
					}
					$call_rate = $callrate_cache[$file][substr($dst,0,$i)][0] + ( ($duration-60) * ($ext_rate) );
				}
			} elseif ($callrate_cache[$file][substr($dst,0,$i)][2] == '30s+s') {
				// 30 second + per second
				if ($duration > 0 && $duration <= 30) {
					$call_rate = ($callrate_cache[$file][substr($dst,0,$i)][0] / 2);
				} elseif ( $duration > 30 && $duration < 60) {
					$call_rate = ($callrate_cache[$file][substr($dst,0,$i)][0] / 2) + (($duration-30) * ($callrate_cache[$file][substr($dst,0,$i)][0] / 60));
				} else {
					$call_rate = $callrate_cache[$file][substr($dst,0,$i)][0] + (($duration-60) * ($callrate_cache[$file][substr($dst,0,$i)][0] / 60));
				}
			} elseif ($callrate_cache[$file][substr($dst,0,$i)][2] == '30s+6s') {
				// 30 second + 6 second
				if ($duration > 0 && $duration <= 30) {
					$call_rate = ($callrate_cache[$file][substr($dst,0,$i)][0] / 2);
				} else {
					$call_rate = ceil($duration / 6) * ($callrate_cache[$file][substr($dst,0,$i)][0] / 10);
				}
			} else {
				//( $callrate_cache[substr($dst,0,$i)][2] == 'm' ) {
				// per minute
				if ($duration >= $callrate_free_interval) {
					// всего минут разговор
					$call_rate = ceil($duration/60);
					// указан доп тариф
					if (isset($callrate_cache[$file][substr($dst,0,$i)][3]) && $callrate_cache[$file][substr($dst,0,$i)][3] && $callrate_cache[$file][substr($dst,0,$i)][3] > 0) {
						$call_rate = $callrate_cache[$file][substr($dst,0,$i)][0] + ( ($call_rate-1) * $callrate_cache[$file][substr($dst,0,$i)][3] );
					} else {
						$call_rate = $call_rate*$callrate_cache[$file][substr($dst,0,$i)][0];
					}					
				}
			}
			return array(substr($dst,0,$i),$callrate_cache[$file][substr($dst,0,$i)][0],$callrate_cache[$file][substr($dst,0,$i)][1],$callrate_cache[$file][substr($dst,0,$i)][2],$call_rate);
		}
	}
	return array (0, 0, 'Неизвестно', 'Неизвестно', 0);
}


