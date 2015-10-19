<?php

/* Recorded file */
function formatFiles($row) {
	global $system_monitor_dir, $system_fax_archive_dir, $system_audio_format, $system_archive_format, $system_fsize_exists, $system_column_name, $system_storage_format;

	/* File name formats, please specify: */
	
	/* 
		caller-called-timestamp.wav 
	*/
	/* 
	$recorded_file = $row['src'] .'-'. $row['dst'] .'-'. $row['call_timestamp']
	*/
	/* ============================================================================ */	

	/* 
		ends at the uniqueid.wav, for example: 
												date-time-uniqueid.wav 
	
		thanks to Beto Reyes
	*/
	/*
	$recorded_file = glob($system_monitor_dir . '/*' . $row['uniqueid'] . '.' . $system_audio_format);
	if (count($recorded_file)>0) {
		$recorded_file = basename($recorded_file[0],".$system_audio_format");
	} else {
		$recorded_file = $row['uniqueid'];
	}
	*/
	/* ============================================================================ */

	/*      This example for multi-directory archive without uniqueid, named by format:
			<date>/<time>_<caller>-<destination>.<filetype>

			example: (tree /var/spool/asterisk/monitor)

		|-- 2012.09.12
		|   |-- 10-37_4952704601-763245.ogg
		|   `-- 10-43_106-79236522173.ogg
		`-- 2012.09.13
			|-- 11-42_101-79016410692.ogg
			|-- 12-43_104-671554.ogg
			`-- 15-49_109-279710.ogg

		Added by BAXMAH (pcm@ritm.omsk.ru)
	*/
	/*
	   $record_datetime = DateTime::createFromFormat('Y-m-d G:i:s', $row['calldate']);

	   $recorded_file = date_format($record_datetime, 'Y.m.d/G-i') .'_'. $row['src'] .'-'. $row['dst'];
	*/
	/* ============================================================================ */

	/*
		This is a multi-dir search script for filenames like "/var/spool/asterisk/monitor/dir1/dir2/dir3/*uniqueid*.*"
		Doesn't matter, WAV, MP3 or other file format, only UNIQID  is  required at the end of the filename 
		;---------------------------------------------------------------------------  
	   example: (tree /var/spool/asterisk/monitor)

    |-- in
    |   |-- 4951234567
    |   |   `-- 20120101_234231_4956401234_to_74951234567_1307542950.0.wav
    |   `-- 4997654321
    |       `-- 20120202_234231_4956401234_to_74997654321_1303542950.0.wav
    `-- out
        |-- msk
        |   `-- 20120125_211231_4956401234_to_74951234567_1307542950.0.wav
        `-- region
            `-- 20120112_211231_4956405570_to_74952210533_1307542950.0.wav

      6 directories, 4 files
		;----------------------------------------------------------------------------
	   added by Dein admin@sadmin.ru         
	*/
	
	/*
	//************ Get a list of subdirectories as array to search by glob function  **************
	if (!function_exists('get_dir_list')) {
		function get_dir_list($dir){
			global $dirlist;			
			$dirlist=array();
			if (!function_exists('find_dirs_recursive')) {
				function find_dirs_recursive($sdir) {
					global $dirlist;
					foreach(glob($sdir) as $filename) {
						//echo $filename;
						if(is_dir($filename)) {
							$dirlist[]=$filename;
							find_dirs_recursive($filename."/*");
						};//endif
					};//endforeach
				}; //endfunc                                                                                               
			};//endif exists
			find_dirs_recursive($dir."/*");
		};//endfunc
	}

	//*************** Main function  ************
	if (!function_exists('find_record_by_uniqid')) {
		function find_record_by_uniqid($path,$uniqid){
			global $dirlist;
			if (sizeof($dirlist) == 0 ){
				get_dir_list($path);
			};//endif size==0

			if (sizeof($dirlist) == 0 ) {return "SOME ERROR, dirlist is empty";};

			$found = "NOTHING FOUND";
			foreach ($dirlist as $curdir) {
				$res=glob($curdir."/*".$uniqid.".*");
				if ($res) {$found=$res[0]; break;};
			};//endforeach

			$res=str_replace($path,"",$found);	//cut $path from full filename 
			
			return $res;			//to be compartable with func. formatFiles($row)

		};//endfunc
	}
	
	$recorded_file = find_record_by_uniqid($system_monitor_dir,$row['uniqueid']);
	
	*/
	
	# uniq_name.mp3
	$recorded_file = $row[$system_column_name];
	$mycalldate_ymd = substr($row['calldate'], 0, 10); // ymd
	$mycalldate_ym = substr($row['calldate'], 0, 7); // ym
	$mycalldate_y = substr($row['calldate'], 0, 4); // y
	$mycalldate_m = substr($row['calldate'], 5, 2); // m
	$mycalldate_d = substr($row['calldate'], 8, 2); // d
	$mydate = date('Y-m-d');

	// -----------------------------------------------
	
	# Файл не найден
	$tmpError = '<td class="record_col"><img class="img_notfound" src="img/record_notfound.png"></td>';

	# Прослушивание и скачивание
	$tmpRec = '<td class="record_col">
					<div class="recordBox">
						<a onclick="showRecord(\'dl.php?f=[_file]\', \''.$row['calldate'].'\');"><img class="img_play" src="img/record_play.png"></a>
						<a href="dl.php?f=[_file]"><img class="img_dl" src="img/record_dl.png"></a>
					</div>
				</td>
					';
	
	# Только скачивание
	$tmpDl = '<td class="record_col">
					<div class="recordBox">
						<a href="dl.php?f=[_file]"><img class="img_dl" src="img/record_dl.png"></a>
					</div>
				</td>
					';
					
	// -----------------------------------------------
	
	# Получение имени файла и пути
	if ($mycalldate_ymd < $mydate && $system_storage_format === 1) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_ym/$mycalldate_ymd/$recorded_file";
	} else if ($mycalldate_ymd < $mydate && $system_storage_format === 2) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_m/$mycalldate_d/$recorded_file";
	} else if ($system_storage_format === 3) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_ym/$mycalldate_ymd/$recorded_file";
	} else if ($system_storage_format === 4) {
		$rec['filename'] = "$mycalldate_y/$mycalldate_m/$mycalldate_d/$recorded_file";
	} else {
		$rec['filename'] = $recorded_file;
	}
	
	$rec['path'] = $system_monitor_dir.'/'.$rec['filename'];
	$rec['filesize'] = file_exists($rec['path']) ? filesize($rec['path'])/1024 : 0;	
	
	# аудио
	if (file_exists($rec['path']) && $recorded_file && $rec['filesize'] >= $system_fsize_exists && preg_match('#(.*)\.'.$system_audio_format.'$#i', $rec['filename'])) {
		$tmpRes = str_replace('[_file]', base64_encode($rec['filename']), $tmpRec);
	}
	# архив
	else if (isset($system_archive_format) && $recorded_file && file_exists($rec['path'].'.'.$system_archive_format) && $rec['filesize'] >= $system_fsize_exists) {
		$tmpRes = str_replace('[_file]', base64_encode($rec['filename'].'.'.$system_archive_format), $tmpDl);
	}
	# факс
	//else if (file_exists($rec['path']) && preg_match('#(.*)\.tiff?$#i', $rec['filename']) && $rec['filesize'] >= $system_fsize_exists) {
	else if (file_exists($rec['path']) && $recorded_file && $rec['filesize'] >= $system_fsize_exists) {
		$tmpRes = str_replace('[_file]', base64_encode($rec['filename']), $tmpDl);
	}
	
	else { 
		$tmpRes = $tmpError; 
	}

	echo $tmpRes;
	
}


/* CDR Table Display Functions */
function formatCallDate($calldate,$uniqueid) {
	//$calldate = date('d.m.Y H:i:s', strtotime($calldate));
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="UID: '.$uniqueid.'">'.$calldate.'</abbr></td>' . PHP_EOL;
}

function formatChannel($channel) {
	global $display_full_channel;
	$chan_type = explode('/', $channel);
	$chan_id = explode('-', $chan_type[1]);
	
	$chan['short'] = $chan_type[0];
	$chan['full'] = $chan_type[0].'/'.$chan_id[0];
	$chan['tooltip'] = 'Канал: '.$chan['full'];
	$chan['txt'] = $chan['short'];
	if (isset($display_full_channel) && $display_full_channel === true) {
		$chan['txt'] = $chan['full'];
	}
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="'.$chan['tooltip'].'">'.$chan['txt'].'</abbr></td>' . PHP_EOL;
}

function formatClid($clid) {
	$clid_only = explode(' <', $clid, 2);
	$clid = htmlspecialchars($clid_only[0]);
	echo '<td class="record_col">'.$clid.'</td>' . PHP_EOL;
}

function formatSrc($src,$clid) {
	global $rev_lookup_url, $rev_min_number_len ;
	if (empty($src)) {
		echo '<td class="record_col">Неизвестно</td>' . PHP_EOL;
	} else {
		$src = htmlspecialchars($src);
		$clid = htmlspecialchars($clid);
		//echo "    <td class=\"record_col\"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip=\"CallerID: $clid\">$src</abbr></td>\n";
		if (is_numeric($src) && strlen($src) >= $rev_min_number_len  && strlen($rev_lookup_url) > 0) {
			$rev = str_replace('%n', $src, $rev_lookup_url);
			echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="CallerID: '.$clid.'"><a href="'.$rev.'" target="reverse">'.$src.'</a></abbr></td>' . PHP_EOL;
		} else {
			echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="CallerID: '.$clid.'">'.$src.'</abbr></td>' . PHP_EOL;
		}
	}
}

function formatApp($app, $lastdata) {
	echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="Приложение: '.$app.'('.$lastdata.')">'.$app.'</abbr></td>' . PHP_EOL;
}

function formatDst($dst, $dcontext) {
	global $rev_lookup_url, $rev_min_number_len ;
	//strlen($dst) == 11 and strlen($rev_lookup_url) > 0 
	if (is_numeric($dst) && strlen($dst) >= $rev_min_number_len  && strlen($rev_lookup_url) > 0) {
		$rev = str_replace('%n', $dst, $rev_lookup_url);
		echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="Контекст назначения: '.$dcontext.'"><a href="'.$rev.'" target="reverse">'.$dst.'</a></abbr></td>' . PHP_EOL;
	} else {
		echo '<td class="record_col"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="Контекст назначения: '.$dcontext.'">'.$dst.'</abbr></td>' . PHP_EOL;
	}
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
	
	$style = ''; // стиль текста для вызовов
	switch ($disposition) {
		case 'ANSWERED':
			$dispTxt = 'Отвечено';
			$img = 'img/status_answer.png';
			$style = 'answer';
			break;
		case 'NO ANSWER':
			$dispTxt = 'Не Отвечено';
			$img = 'img/status_noanswer.png';
			$style = 'noanswer';
			break;
		case 'BUSY':
			$dispTxt = 'Занято';
			$img = 'img/status_busy.png';
			$style = 'busy';
			break;
		case 'FAILED':
			$dispTxt = 'Ошибка';
			$img = 'img/status_failed.png';
			$style = 'failed';
			break;
		default:
			$dispTxt = $disposition;
	}

	echo '<td class="record_col '.$style.'"><img class="status" src="'.$img.'"><abbr class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="AMA Flag: '.$amaflags.'">'.$dispTxt.'</abbr></td>' . PHP_EOL;
}

function formatDuration($duration, $billsec) {
	$duration = sprintf('%02d', intval($duration/60)).':'.sprintf('%02d', intval($duration%60));
	$billduration = sprintf('%02d', intval($billsec/60)).':'.sprintf('%02d', intval($billsec%60));
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
function is_blank($value) {
	return empty($value) && !is_numeric($value);
}

/* 
	Money format

*/
// cents: 0=never, 1=if needed, 2=always
// title: title to show
function formatMoney($number, $cents = 2, $title = '') {
	global $callrate_currency;
	if (is_numeric($number)) { // a number
		if (!$number) { // zero
			$money = ($cents == 2 ? '0.00' : '0'); // output zero
		} else { // value
			if (floor($number) == $number) { // whole number
				$money = number_format($number, ($cents == 2 ? 2 : 0)); // format
			} else { // cents
				$money = number_format(round($number, 2), ($cents == 0 ? 0 : 2)); // format
			} // integer or decimal
		} // value
		
		if ($title) {
			$title = ' class="simptip-position-top simptip-smooth simptip-fade" data-tooltip="'.$title.'"';
		}
		echo '<td class="chart_data"><span'.$title.'>'.$money.'</span>'.$callrate_currency.'</td>' . PHP_EOL;
	} else {
		echo '<td class="chart_data">&nbsp;</td>\n' . PHP_EOL;
	}
} // formatMoney

/* 
	CallRate
	return callrate array [ areacode, rate, description, bill type, total_rate] 
*/
function callrates($dst,$duration,$file) {
	global $callrate_csv_file, $callrate_cache, $callrate_free_interval;

	if (strlen($file) == 0) {
		$file = $callrate_csv_file;
		if (strlen($file) == 0) {
			return array('','','','','');
		}
	}
	
	if (!array_key_exists($file, $callrate_cache)) {
		$callrate_cache[$file] = array();
		$fr = fopen($file, "r") or die("Can not open callrate file ($file).");
		while(($fr_data = fgetcsv($fr, 1000, ",")) !== false) {
			$callrate_cache[$file][$fr_data[0]] = array($fr_data[1], $fr_data[2], $fr_data[3], $fr_data[4]);
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
				else if ( $duration < 60) {
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


