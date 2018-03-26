<?php 
//error_reporting(E_ALL | E_STRICT); ini_set('display_errors', 'On');
//$start_timer = microtime(true);

require_once 'inc/load.php';

if ( !isset($_POST['form_submitted']) ) {
	require_once 'templates/header.php';
	require_once 'templates/form.php';
}

$dbh = dbConnect();

// Connecting, selecting database
foreach ( array_keys($_REQUEST) as $key ) {
	$_REQUEST[$key] = preg_replace('/;/', ' ', $_REQUEST[$key]);
	$_REQUEST[$key] = substr($dbh->quote($_REQUEST[$key]),1,-1);
}

$startmonth = is_blank($_REQUEST['startmonth']) ? date('m') : sprintf('%02d', $_REQUEST['startmonth']);
$startyear = is_blank($_REQUEST['startyear']) ? date('Y') : $_REQUEST['startyear'];

if (is_blank($_REQUEST['startday'])) {
	$startday = '01';
} elseif (isset($_REQUEST['startday']) && ($_REQUEST['startday'] > date('t', strtotime("$startyear-$startmonth-01")))) {
	$startday = $_REQUEST['startday'] = date('t', strtotime("$startyear-$startmonth"));
} else {
	$startday = sprintf('%02d',$_REQUEST['startday']);
}
$starthour = is_blank($_REQUEST['starthour']) ? '00' : sprintf('%02d',$_REQUEST['starthour']);
$startmin = is_blank($_REQUEST['startmin']) ? '00' : sprintf('%02d',$_REQUEST['startmin']);

$startdate = "'$startyear-$startmonth-$startday $starthour:$startmin:00'";
$start_timestamp = mktime( $starthour, $startmin, 59, $startmonth, $startday, $startyear );

$endmonth = is_blank($_REQUEST['endmonth']) ? date('m') : sprintf('%02d', $_REQUEST['endmonth']); 
$endyear = is_blank($_REQUEST['endyear']) ? date('Y') : $_REQUEST['endyear'];  

if (is_blank($_REQUEST['endday']) || (isset($_REQUEST['endday']) && ($_REQUEST['endday'] > date('t', strtotime("$endyear-$endmonth-01"))))) {
	$endday = $_REQUEST['endday'] = date('t', strtotime("$endyear-$endmonth"));
} else {
	$endday = sprintf('%02d',$_REQUEST['endday']);
}
$endhour = is_blank($_REQUEST['endhour']) ? '23' : sprintf('%02d',$_REQUEST['endhour']);
$endmin = is_blank($_REQUEST['endmin']) ? '59' : sprintf('%02d',$_REQUEST['endmin']);

$enddate = "'$endyear-$endmonth-$endday $endhour:$endmin:59'";
$end_timestamp = mktime( $endhour, $endmin, 59, $endmonth, $endday, $endyear );


# Asterisk regexp2sqllike
if ( is_blank($_REQUEST['src']) ) {
	$src_number = null;
} else {
	$src_number = asteriskregexp2sqllike( 'src', '' );
}

if ( is_blank($_REQUEST['dst']) ) {
	$dst_number = null;
} else {
	$dst_number = asteriskregexp2sqllike( 'dst', '' );
}

if ( is_blank($_REQUEST['did']) ) {
	$did_number = null;
} else {
	$did_number = asteriskregexp2sqllike( 'did', '' );
}

$date_range = "calldate BETWEEN $startdate AND $enddate";
$mod_vars['channel'][] = is_blank($_REQUEST['channel']) ? null : $_REQUEST['channel'];
$mod_vars['channel'][] = empty($_REQUEST['channel_mod']) ? null : $_REQUEST['channel_mod'];
$mod_vars['channel'][] = empty($_REQUEST['channel_neg']) ? null : $_REQUEST['channel_neg'];
$mod_vars['src'][] = $src_number;
$mod_vars['src'][] = empty($_REQUEST['src_mod']) ? null : $_REQUEST['src_mod'];
$mod_vars['src'][] = empty($_REQUEST['src_neg']) ? null : $_REQUEST['src_neg'];
$mod_vars['clid'][] = is_blank($_REQUEST['clid']) ? null : $_REQUEST['clid'];
$mod_vars['clid'][] = empty($_REQUEST['clid_mod']) ? null : $_REQUEST['clid_mod'];
$mod_vars['clid'][] = empty($_REQUEST['clid_neg']) ? null : $_REQUEST['clid_neg'];
$mod_vars['dstchannel'][] = is_blank($_REQUEST['dstchannel']) ? null : $_REQUEST['dstchannel'];
$mod_vars['dstchannel'][] = empty($_REQUEST['dstchannel_mod']) ? null : $_REQUEST['dstchannel_mod'];
$mod_vars['dstchannel'][] = empty($_REQUEST['dstchannel_neg']) ? null : $_REQUEST['dstchannel_neg'];
$mod_vars['dst'][] = $dst_number;
$mod_vars['dst'][] = empty($_REQUEST['dst_mod']) ? null : $_REQUEST['dst_mod'];
$mod_vars['dst'][] = empty($_REQUEST['dst_neg']) ? null : $_REQUEST['dst_neg'];
$mod_vars['did'][] = $did_number;
$mod_vars['did'][] = empty($_REQUEST['did_mod']) ? null : $_REQUEST['did_mod'];
$mod_vars['did'][] = empty($_REQUEST['did_neg']) ? null : $_REQUEST['did_neg'];
$mod_vars['userfield'][] = is_blank($_REQUEST['userfield']) ? null : $_REQUEST['userfield'];
$mod_vars['userfield'][] = empty($_REQUEST['userfield_mod']) ? null : $_REQUEST['userfield_mod'];
$mod_vars['userfield'][] = empty($_REQUEST['userfield_neg']) ? null : $_REQUEST['userfield_neg'];
$mod_vars['accountcode'][] = is_blank($_REQUEST['accountcode']) ? null : $_REQUEST['accountcode'];
$mod_vars['accountcode'][] = empty($_REQUEST['accountcode_mod']) ? null : $_REQUEST['accountcode_mod'];
$mod_vars['accountcode'][] = empty($_REQUEST['accountcode_neg']) ? null : $_REQUEST['accountcode_neg'];
$result_limit = is_blank($_REQUEST['limit']) ? Config::get('display.main.result_limit') : intval($_REQUEST['limit']);
$db_table_name = Config::get('db.table');
$search_condition = '';
$callrate_cache = array();

if ( strlen($cdr_user_name) > 0 ) {
	$cdr_user_name = asteriskregexp2sqllike( 'cdr_user_name', substr($dbh->quote($cdr_user_name),1,-1) );
	if ( isset($mod_vars['cdr_user_name']) && $mod_vars['cdr_user_name'][2] == 'asterisk-regexp' ) {
		$cdr_user_name = " AND ( dst RLIKE '$cdr_user_name' or src RLIKE '$cdr_user_name' )";
	} else {
		$cdr_user_name = " AND ( dst = '$cdr_user_name' or src = '$cdr_user_name' )";
	}
}

// Build the "WHERE" part of the query

foreach ($mod_vars as $key => $val) {
	if (is_blank($val[0])) {
		unset($_REQUEST[$key.'_mod']);
		$$key = null;
	} else {
		$pre_like = '';
		if ( $val[2] == 'true' ) {
			$pre_like = ' NOT ';
		}
		switch ($val[1]) {
			case "contains":
				$$key = "$search_condition $key $pre_like LIKE '%$val[0]%'";
			break;
			case "ends_with":
				$$key = "$search_condition $key $pre_like LIKE '%$val[0]'";
			break;
			case "exact":
				if ( $val[2] == 'true' ) {
					$$key = "$search_condition $key != '$val[0]'";
				} else {
					$$key = "$search_condition $key = '$val[0]'";
				}
			break;
			case "asterisk-regexp":
				$ast_dids = preg_split('/\s*,\s*/', $val[0], -1, PREG_SPLIT_NO_EMPTY);
				$ast_key = '';
				foreach ($ast_dids as $did) {
					if (strlen($ast_key) > 0 ) {
						if ( $pre_like == ' NOT ' ) {
							$ast_key .= " and ";
						} else {
							$ast_key .= " or ";
						}
						if ( '_' == substr($did,0,1) ) {
							$did = substr($did,1);
						}
					}
					$ast_key .= " $key $pre_like RLIKE '^$did\$'";
				}
				$$key = "$search_condition ( $ast_key )";
			break;
			case "begins_with":
			default:
				$$key = "$search_condition $key $pre_like LIKE '$val[0]%'";
		}
		if ( $search_condition == '' ) {
			if ( isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any' ) {
				$search_condition = ' OR ';
			} else {
				$search_condition = ' AND ';
			}
		}
	}
}

if ( isset($_REQUEST['disposition_neg']) && $_REQUEST['disposition_neg'] == 'true' ) {
	$disposition = (empty($_REQUEST['disposition']) || $_REQUEST['disposition'] == 'all') ? null : "$search_condition disposition != '$_REQUEST[disposition]'";
} else {
	$disposition = (empty($_REQUEST['disposition']) || $_REQUEST['disposition'] == 'all') ? null : "$search_condition disposition = '$_REQUEST[disposition]'";
}

if ( $search_condition == '' ) {
	if ( isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any' ) {
		$search_condition = ' OR ';
	} else {
		$search_condition = ' AND ';
	}
}

$where = "$channel $src $clid $did $dstchannel $dst $userfield $accountcode $disposition";

if ( isset($_REQUEST['lastapp_neg']) && $_REQUEST['lastapp_neg'] == 'true' ) {
	$lastapp = (empty($_REQUEST['lastapp']) || $_REQUEST['lastapp'] == 'all') ? null : "lastapp != '$_REQUEST[lastapp]'";
} else {
	$lastapp = (empty($_REQUEST['lastapp']) || $_REQUEST['lastapp'] == 'all') ? null : "lastapp = '$_REQUEST[lastapp]'";
}

if ( strlen($lastapp) > 0 ) {
	if ( strlen($where) > 8 ) {
		$where = "$where $search_condition $lastapp";
	} else {
		$where = "$where $lastapp";
	}
}

$duration = !isset($_REQUEST['dur_min']) || is_blank($_REQUEST['dur_max']) ? null : "duration BETWEEN '$_REQUEST[dur_min]' AND '$_REQUEST[dur_max]'";

if ( strlen($duration) > 0 ) {
	if ( strlen($where) > 8 ) {
		$where = "$where $search_condition $duration";
	} else {
		$where = "$where $duration";
	}
}

$billsec = (!isset($_REQUEST['bill_min']) || is_blank($_REQUEST['bill_max'])) ? null : "billsec BETWEEN '$_REQUEST[bill_min]' AND '$_REQUEST[bill_max]'";

if ( strlen($billsec) > 0 ) {
	if ( strlen($where) > 8 ) {
		$where = "$where $search_condition $billsec";
	} else {
		$where = "$where $billsec";
	}
}

if ( strlen($where) > 9 ) {
	$where = "WHERE $date_range AND ( $where ) $cdr_user_name";
} else {
	$where = "WHERE $date_range $cdr_user_name";
}

$use_callrates = false;
if ( Config::get('callrate.enabled') == 1 && is_file(Config::get('callrate.csv_file')) ) {
	$use_callrates = true;	
}

$order = empty($_REQUEST['order']) ? 'ORDER BY calldate' : "ORDER BY $_REQUEST[order]";
$sort = empty($_REQUEST['sort']) ? 'DESC' : $_REQUEST['sort'];
$group = empty($_REQUEST['group']) ? 'day' : $_REQUEST['group'];

// CSV отчет
if ( isset($_REQUEST['need_csv']) && $_REQUEST['need_csv'] == 'true' ) {
	$csv_date = time();
	$csv_fname = 'report__' . date('Y-m-d_H-i-s', $csv_date) . '_' . md5($csv_date.'-'.$where) . '.csv';
	$csv_delim = Config::get('system.csv_delim');
	if ( !is_file(Config::get('system.tmp_dir').'/'.$csv_fname) ) {
		$handle = fopen(Config::get('system.tmp_dir').'/'.$csv_fname, "w");
		$query = "SELECT * FROM $db_table_name $where $order $sort LIMIT $result_limit";
		try {
			$sth = $dbh->query($query);
		}
		catch (PDOException $e) {
			print $e->getMessage();
		}
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}

		fwrite($handle, implode($csv_delim,
			array(
				'calldate',
				'clid',
				'src',
				'did',
				'dst',
				'dcontext',
				'channel',
				'dstchannel',
				'lastapp',
				'lastdata',
				'duration',
				'billsec',
				'disposition',
				'amaflags',
				'accountcode',
				'peeraccount',
				'uniqueid',
				'userfield',
				'linkedid',
				'sequence',
			))
		);
		
		if ( $use_callrates === true ) {
			fwrite($handle, $csv_delim.'callrate'.$csv_delim.'callrate_dst');
		}
		fwrite($handle, "\n");
		
		while ( $row = $sth->fetch(PDO::FETCH_ASSOC) ) {
			$csv_line[0] 	= $row['calldate'];
			$csv_line[1] 	= $row['clid'];
			$csv_line[2] 	= $row['src'];
			$csv_line[3] 	= isset($row['did']) ? $row['did'] : '';
			$csv_line[4] 	= $row['dst'];
			$csv_line[5] 	= $row['dcontext'];
			$csv_line[6]	= $row['channel'];
			$csv_line[7] 	= $row['dstchannel'];
			$csv_line[8] 	= $row['lastapp'];
			$csv_line[9]	= $row['lastdata'];
			$csv_line[10]	= $row['duration'];
			$csv_line[11]	= $row['billsec'];
			$csv_line[12]	= $row['disposition'];
			$csv_line[13]	= $row['amaflags'];
			$csv_line[14]	= $row['accountcode'];
			$csv_line[15]	= isset($row['peeraccount']) ? $row['peeraccount'] : '';
			$csv_line[16]	= $row['uniqueid'];
			$csv_line[17]	= $row['userfield'];
			$csv_line[18]	= isset($row['linkedid']) ? $row['linkedid'] : '';
			$csv_line[19]	= isset($row['sequence']) ? $row['sequence'] : '';
			$data = '';
			if ( $use_callrates === true ) {
				$rates = callrates( $row['dst'],$row['billsec'],Config::get('callrate.csv_file') );
				$csv_line[20] = $rates[4];
				$csv_line[21] = $rates[2];
			}
			for ($i = 0; $i < count($csv_line); $i++) {
				$csv_line[$i] = str_replace( array( "\n", "\r" ), '', $csv_line[$i]);
				/* If the string contains a comma, enclose it in double-quotes. */
				if (strpos($csv_line[$i], $csv_delim) !== FALSE) { 	// ,
					$csv_line[$i] = "\"" . $csv_line[$i] . "\"";
				}
				if ($i != count($csv_line) - 1) {
					$data = $data . $csv_line[$i] . $csv_delim;
				} else {
					$data = $data . $csv_line[$i];
				}
			}
			unset($csv_line);
			fwrite($handle, "$data\n");
		}
		fclose($handle);
		$sth = null;
	}
	echo '<p class="dl_csv_box"><button class="dl_csv btn btn-info" data-filepath="'.base64_encode($csv_fname).'">Скачать CSV отчет</button></p>';
}

if ( isset($_REQUEST['need_html']) && $_REQUEST['need_html'] == 'true' ) {
	$query = "SELECT count(*) FROM $db_table_name $where LIMIT $result_limit";
	try {
		$sth = $dbh->query($query);
	}
	catch (PDOException $e) {
		print $e->getMessage();
	}
	if (!$sth) {
		echo "\nPDO::errorInfo():\n";
		print_r($dbh->errorInfo());
	} else {
		$tot_calls_raw = $sth->fetchColumn();
		$sth = null;
	}
	if ( isset($tot_calls_raw) && $tot_calls_raw ) {

		$i = Config::get('display.main.header_step') - 1;

		try {
			
			$query = "SELECT *, unix_timestamp(calldate) as call_timestamp FROM $db_table_name $where $order $sort LIMIT $result_limit";
			$sth = $dbh->query($query);
			if (!$sth) {
				echo "\nPDO::errorInfo():\n";
				print_r($dbh->errorInfo());
			}

			$rawresult = $sth->fetchAll(PDO::FETCH_ASSOC);
			$filtered_count = 0;
			# Удаление дублирующихся записей в Asterisk 13
			if ( Config::get('display.main.duphide') == 1 ) {
				foreach($rawresult as $val) {
					$superresult[$val['uniqueid'].'-'.$val['disposition']] = $val;
				}
				foreach($superresult as $val) {
					if (
						in_array($val['disposition'], array('ANSWERED', 'NORMAL_CLEARING', 'NORMAL_UNSPECIFIED'))
						&& array_key_exists($val['uniqueid'].'-'.'NO ANSWER' , $superresult) 
					)
					{
						unset ($superresult[$val['uniqueid'].'-'.'NO ANSWER']);
					}
				}
				$filtered_count = count($rawresult) - count($superresult);
			} else {
				$superresult = $rawresult;
			}
			if ( $tot_calls_raw > $result_limit ) {
				echo '<p class="center title">Показаны '. ($result_limit - $filtered_count) .' из '. $tot_calls_raw;
				echo Config::get('display.main.duphide') == 1 ? ', отфильтровано ' . $filtered_count : '';
				echo ' записей </p><table class="cdr">';
			} else {
				echo '<p class="center title">Найдено '. $tot_calls_raw;
				echo Config::get('display.main.duphide') == 1 ? ', отфильтровано ' . $filtered_count : '';
				echo ' записей </p><table class="cdr">';
			}
			foreach ( $superresult as $key => $row ) {			
				++$i;
				if ( $i == Config::get('display.main.header_step') ) {
				?>
					<tr>
						<th rowspan="2" class="record_col">Дата и время</th>
						<th rowspan="2" class="record_col">Статус</th>
						<th rowspan="2" class="record_col">Кто звонил</th>
						<th rowspan="2" class="record_col">Куда звонили</th>
						<?php
						if ( Config::exists('display.column.did') && Config::get('display.column.did') == 1 ) {
							echo '<th rowspan="2" class="record_col">DID</th>';
						}
						if ( Config::get('display.column.durwait') == 1
							|| Config::get('display.column.billsec') == 1
							|| Config::get('display.column.duration') == 1
						) {
							$colspan_duration = array_sum(array(
								Config::get('display.column.durwait'),
								Config::get('display.column.billsec'),
								Config::get('display.column.duration'),
							));
							echo '<th colspan="'.$colspan_duration.'" class="record_col">Длительность</th>';
						}						
						if ( $use_callrates === true ) {
							if ( Config::exists('display.column.callrates') && Config::get('display.column.callrates') == 1 ) {
								echo '<th rowspan="2" class="record_col">Тариф</th>';
							}
							// Показать Направление
							if ( Config::exists('display.column.callrates_dst') && Config::get('display.column.callrates_dst') == 1 ) {
								echo '<th rowspan="2" class="record_col">Направление</th>';
							}
						}
						if ( Config::exists('display.column.lastapp') && Config::get('display.column.lastapp') == 1 ) {
							echo '<th rowspan="2" class="record_col">Приложение</th>';
						}
						if ( Config::exists('display.column.channel') && Config::get('display.column.channel') == 1 ) {
							echo '<th rowspan="2" class="record_col">Вх. канал</th>';
						}
						if ( Config::exists('display.column.clid') && Config::get('display.column.clid') == 1 ) {
							echo '<th rowspan="2" class="record_col">CallerID</th>';
						}
						if ( Config::exists('display.column.dstchannel') && Config::get('display.column.dstchannel') == 1 ) {
							echo '<th rowspan="2" class="record_col">Исх. канал</th>';
						}
						if ( Config::exists('display.column.file') && Config::get('display.column.file') == 1 ) {
							echo '<th rowspan="2" class="record_col">Запись</th>';
						}
						if ( Config::exists('display.column.accountcode') && Config::get('display.column.accountcode') == 1 ) {
							echo '<th rowspan="2" class="record_col">Аккаунт</th>';
						}
						if ( Config::exists('display.column.userfield') && Config::get('display.column.userfield') == 1 ) {
							echo '<th rowspan="2" class="record_col">Комментарий</th>';
						}
						?>
					</tr>
					<tr>
						<?php
						if ( Config::exists('display.column.durwait') && Config::get('display.column.durwait') == 1 ) {
							echo '<th class="record_col">ожидание ответа</th>';
						}
						if ( Config::exists('display.column.billsec') && Config::get('display.column.billsec') == 1 ) {
							echo '<th class="record_col">обработка звонка</th>';
						}
						if ( Config::exists('display.column.duration') && Config::get('display.column.duration') == 1 ) {
							echo '<th class="record_col">полная</th>';
						}						
						?>
					</tr>
					<?php
					$i = 0;
				}
				
				$file_params = getFileParams($row);
				echo '<tr class="record record_cdr" data-id="'.$row['id'].'" data-filepath="'.$file_params['path'].'">';
				formatCallDate($row['calldate'],$row['uniqueid']);
				formatDisposition($row['disposition'], $row['amaflags']);
				formatSrc($row['src'],$row['clid']);
				formatDst($row['dst'], $row['dcontext'] );
				if ( Config::exists('display.column.did') && Config::get('display.column.did') == 1 ) {
					if ( isset($row['did']) && strlen($row['did']) ) {
						formatDst($row['did'], $row['dcontext'] . ' # ' . $row['dst']);
					} else {
						formatDst('', $row['dcontext']);
					}					
				}
				if ( Config::exists('display.column.durwait') && Config::get('display.column.durwait') == 1 ) {
					formatDurWait($row['duration'], $row['billsec']);
				}
				if ( Config::exists('display.column.billsec') && Config::get('display.column.billsec') == 1 ) {
					formatBillSec($row['duration'], $row['billsec']);
				}
				if ( Config::exists('display.column.duration') && Config::get('display.column.duration') == 1 ) {
					formatDuration($row['duration'], $row['billsec']);
				}				
				if ( $use_callrates === true ) {
					$rates = callrates( $row['dst'],$row['billsec'],Config::get('callrate.csv_file') );
					if ( Config::exists('display.column.callrates') && Config::get('display.column.callrates') == 1 ) {
						formatMoney($rates[4],2,htmlspecialchars($rates[2]));
					}
					if ( Config::exists('display.column.callrates_dst') && Config::get('display.column.callrates_dst') == 1 ) {
						echo '<td>'. htmlspecialchars($rates[2]) . '</td>';
					}
				}
				if ( Config::exists('display.column.lastapp') && Config::get('display.column.lastapp') == 1 ) {
					formatApp($row['lastapp'], $row['lastdata']);
				}
				if ( Config::exists('display.column.channel') && Config::get('display.column.channel') == 1 ) {
					formatChannel($row['channel']);
				}
				if ( Config::exists('display.column.clid') && Config::get('display.column.clid') == 1 ) {
					formatClid($row['clid']);
				}
				if ( Config::exists('display.column.dstchannel') && Config::get('display.column.dstchannel') == 1 ) {
					formatChannel($row['dstchannel']);
				}
				if ( Config::exists('display.column.file') && Config::get('display.column.file') == 1 ) {
					formatFiles($row, $file_params);
				}
				if ( Config::exists('display.column.accountcode') && Config::get('display.column.accountcode') == 1 ) {
					formatAccountCode($row['accountcode']);
				}
				if ( Config::exists('display.column.userfield') && Config::get('display.column.userfield') == 1 ) {
					formatUserField($row['userfield']);
				}
				echo '</tr>';
			}
			
		}
		catch (PDOException $e) {
			print $e->getMessage();
		}
		echo '</table>';
		$sth = null;
	}
}

// NEW GRAPHS
$group_by_field = $group;
// ConcurrentCalls
$group_by_field_php = array( '', 32, '' );

switch ($group) {
	case "disposition_by_day":
		$graph_col_title = 'Статус по дням';
		$group_by_field_php = array('%Y-%m-%d / ',17,'');
		$group_by_field = "CONCAT(DATE_FORMAT(calldate, '$group_by_field_php[0]'),disposition)";
	break;
	case "disposition_by_hour":
		$graph_col_title = 'Статус по часам';
		$group_by_field_php = array( '%Y-%m-%d %H / ', 20, '' );
		$group_by_field = "CONCAT(DATE_FORMAT(calldate, '$group_by_field_php[0]'),disposition)";
	break;
	case "disposition":
		$graph_col_title = 'Статус';
	break;
	case "dcontext":
		$graph_col_title = 'Контекст';
	break;
	case "accountcode":
		$graph_col_title = 'Код аккаунта';
	break;
	case "dst":
		$graph_col_title = 'Куда звонили';
	break;
	case "did":
		$graph_col_title = 'DID';
	break;
	case "src":
		$graph_col_title = 'Кто звонил';
	break;
	case "clid":
		$graph_col_title = 'CallerID';
	break;
	case "userfield":
		$graph_col_title = 'Комментарий';
	break;
	case "hour":
		$group_by_field_php = array( '%Y-%m-%d %H', 13, '' );
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = 'Час';
	break;
	case "hour_of_day":
		$group_by_field_php = array('%H',2,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = 'Час дня';
	break;
	case "week":
		$group_by_field_php = array('%V',2,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]') ";
		$graph_col_title = 'Неделя ( ПН-ВС )';
	break;
	case "month":
		$group_by_field_php = array('%Y-%m',7,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = 'Месяц';
	break;
	case "day_of_week":
		$group_by_field_php = array('%w - %A',20,'');
		$group_by_field = "DATE_FORMAT( calldate, '%w - %W' )";
		$graph_col_title = 'День недели';
	break;
	case "minutes1":
		$group_by_field_php = array( '%Y-%m-%d %H:%M', 16, '' );
		$group_by_field = "DATE_FORMAT(calldate, '%Y-%m-%d %H:%i')";
		$graph_col_title = 'Минута';
	break;
	case "minutes10":
		$group_by_field_php = array('%Y-%m-%d %H:%M',15,'0');
		$group_by_field = "CONCAT(SUBSTR(DATE_FORMAT(calldate, '%Y-%m-%d %H:%i'),1,15), '0')";
		$graph_col_title = '10 минут';
	break;
	case "day":
	default:
		$group_by_field_php = array('%Y-%m-%d',10,'');
		$group_by_field = "DATE_FORMAT(calldate, '$group_by_field_php[0]')";
		$graph_col_title = 'День';
}

if ( isset($_REQUEST['need_chart']) && $_REQUEST['need_chart'] == 'true' ) {
	$query2 = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, sum(duration) AS total_duration FROM $db_table_name $where GROUP BY group_by_field ORDER BY group_by_field ASC LIMIT $result_limit";

	$tot_calls = 0;
	$tot_duration = 0;
	$max_calls = 0;
	$max_duration = 0;
	$tot_duration_secs = 0;
	$result_array = array();

	try {
		$sth = $dbh->query($query2);
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
		while ($row = $sth->fetch(PDO::FETCH_NUM)) {
			$tot_duration_secs += $row[2];
			$tot_calls += $row[1];
			if ( $row[1] > $max_calls ) {
				$max_calls = $row[1];
			}
			if ( $row[2] > $max_duration ) {
				$max_duration = $row[2];
			}
			array_push($result_array,$row);
		}
	}
	catch (PDOException $e) {
		print $e->getMessage();
	}
	$sth = null;
	$tot_duration = sprintf('%02d', intval($tot_duration_secs/60)).':'.sprintf('%02d', intval($tot_duration_secs%60));

	if ( $tot_calls ) {
		echo '<p class="center title">График по '.$graph_col_title.'</p><table class="cdr">
		<tr>
			<th class="end_col">'. $graph_col_title . '</th>
			<th class="center_col">Всего звонков: '. $tot_calls .' | Максимум звонков: '. $max_calls .' | Общая длительность: '. $tot_duration .'</th>
			<th class="end_col">Ср. время звонка</th>
		</tr>';
	
		foreach ($result_array as $row) {
			$avg_call_time = sprintf('%02d', intval(($row[2]/$row[1])/60)).':'.sprintf('%02d', intval($row[2]/$row[1]%60));
			$bar_calls = $row[1]/$max_calls*100;
			$percent_tot_calls = intval($row[1]/$tot_calls*100);
			$bar_duration = $row[2]/$max_duration*100;
			$percent_tot_duration = intval($row[2]/$tot_duration_secs*100);
			$html_duration = sprintf('%02d', intval($row[2]/60)).':'.sprintf('%02d', intval($row[2]%60));
			echo '<tr>';
			echo "<td class=\"end_col\">$row[0]</td><td class=\"center_col\"><div class=\"bar_calls\" style=\"width : $bar_calls%\">$row[1] - $percent_tot_calls%</div><div class=\"bar_duration\" style=\"width : $bar_duration%\">$html_duration - $percent_tot_duration%</div></td><td class=\"chart_data\">$avg_call_time</td>";
			echo '</tr>';
		}
		echo "</table>";
	}
}

if ( isset($_REQUEST['need_minutes_report']) && $_REQUEST['need_minutes_report'] == 'true' ) {
	$query2 = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, sum(duration), sum(billsec) AS total_duration FROM $db_table_name $where GROUP BY group_by_field ORDER BY group_by_field ASC LIMIT $result_limit";

	$tot_calls = 0;
	$tot_duration = 0;

	echo '<p class="center title">Расход минут по '.$graph_col_title.'</p><table class="cdr">
		<tr>
			<th class="end_col">'. $graph_col_title . '</th>
			<th class="end_col">Кол-во звонков</th>
			<th class="end_col">Минут по биллингу</th>
			<th class="end_col">Ср. время звонка</th>
		</tr>';

	try {
		$sth = $dbh->query($query2);
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
		while ($row = $sth->fetch(PDO::FETCH_NUM)) {
			$html_duration = sprintf('%02d', intval($row[3]/60)).':'.sprintf('%02d', intval($row[3]%60));
			$html_duration_avg	= sprintf('%02d', intval(($row[3]/$row[1])/60)).':'.sprintf('%02d', intval(($row[3]/$row[1])%60));

			echo '<tr class="record">';
			echo "<td class=\"end_col\">$row[0]</td><td class=\"chart_data\">$row[1]</td><td class=\"chart_data\">$html_duration</td><td class=\"chart_data\">$html_duration_avg</td>";
			echo '</tr>';
			
			$tot_duration += $row[3];
			$tot_calls += $row[1];
		}
	}
	catch (PDOException $e) {
		print $e->getMessage();
	}
	$sth = null;
	
	$html_duration = sprintf('%02d', intval($tot_duration/60)).':'.sprintf('%02d', intval($tot_duration%60));
	$html_duration_avg = sprintf( '%02d', ($tot_calls ? intval(($tot_duration/$tot_calls)/60) : 0) ).':'.sprintf( '%02d', ($tot_calls ? intval(($tot_duration/$tot_calls)%60) : 0) );

	echo '<tr>';
	echo "<th class=\"chart_data\">Всего</th><th class=\"chart_data\">$tot_calls</th><th class=\"chart_data\">$html_duration</th><th class=\"chart_data\">$html_duration_avg</th>";
	echo '</tr>';
	echo '</table>';
}

if ( isset($_REQUEST['need_chart_cc']) && $_REQUEST['need_chart_cc'] == 'true' ) {
	$date_range = "( (calldate BETWEEN $startdate AND $enddate) or (calldate + interval duration second  BETWEEN $startdate AND $enddate) or ( calldate + interval duration second >= $enddate AND calldate <= $startdate ) )";
	$where = "$channel $src $clid $dst $dstchannel $dst $userfield $accountcode $disposition $lastapp $duration $cdr_user_name";
	
	if ( strlen(trim($where)) > 1 ) {
		$where = "WHERE $date_range AND ( $where )";
	} else {
		$where = "WHERE $date_range";
	}

	$tot_calls = 0;
	$max_calls = 0;
	$result_array_cc = array();
	$result_array = array();

	if ( strpos($group_by_field,'DATE_FORMAT') === false ) {
		/* not date time fields */
		$query3 = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, unix_timestamp(calldate) AS ts, duration FROM $db_table_name $where GROUP BY group_by_field, unix_timestamp(calldate) ORDER BY group_by_field ASC LIMIT $result_limit";
		
		try {
			$sth = $dbh->query($query3);
			if (!$sth) {
				echo "\nPDO::errorInfo():\n";
				print_r($dbh->errorInfo());
			}
			$group_by_str = '';
			while ($row = $sth->fetch(PDO::FETCH_NUM)) {
				if ( $group_by_str != $row[0] ) {
					$group_by_str = $row[0];
					$result_array = array();
				}
				for ( $i=$row[2]; $i<=$row[2]+$row[3]; ++$i ) {
					if ( isset($result_array[ "$i" ]) ) {
						$result_array[ "$i" ] += $row[1];
					} else {
						$result_array[ "$i" ] = $row[1];
					}
					if ( $max_calls < $result_array[ "$i" ] ) {
						$max_calls = $result_array[ "$i" ];
					}
					if ( ! isset($result_array_cc[ $row[0] ]) || $result_array_cc[ $row[0] ][1] < $result_array[ "$i" ] ) {
						$result_array_cc[ "$row[0]" ][0] = $i;
						$result_array_cc[ "$row[0]" ][1] = $result_array[ "$i" ];
					}
				}
				$tot_calls += $row[1];
			}
		}
		catch (PDOException $e) {
			print $e->getMessage();
		}
		$sth = null;
	} else {
		/* data fields */
		$query3 = "SELECT unix_timestamp(calldate) AS ts, duration FROM $db_table_name $where ORDER BY unix_timestamp(calldate) ASC LIMIT $result_limit";
		$group_by_str = '';
		
		try {
			$sth = $dbh->query($query3);
			if (!$sth) {
				echo "\nPDO::errorInfo():\n";
				print_r($dbh->errorInfo());
			}
			while ($row = $sth->fetch(PDO::FETCH_NUM)) {
				$group_by_str_cur = substr(strftime($group_by_field_php[0],$row[0]),0,$group_by_field_php[1]) . $group_by_field_php[2];
				if ( $group_by_str_cur != $group_by_str ) {
					if ( $group_by_str ) {
						for ( $i=$start_timestamp; $i<$row[0]; ++$i ) {
							if ( ! isset($result_array_cc[ "$group_by_str" ]) || ( isset($result_array["$i"]) && $result_array_cc[ "$group_by_str" ][1] < $result_array["$i"] ) ) {
								$result_array_cc[ "$group_by_str" ][0] = $i;
								$result_array_cc[ "$group_by_str" ][1] = isset($result_array["$i"]) ? $result_array["$i"] : 0;
							}
							unset( $result_array[$i] );
						}
						$start_timestamp = $row[0];
					}
					$group_by_str = $group_by_str_cur;
				}
				for ( $i=$row[0]; $i<=$row[0]+$row[1]; ++$i ) {
					if ( isset($result_array["$i"]) ) {
						++$result_array["$i"];
					} else {
						$result_array["$i"]=1;
					}
					if ( $max_calls < $result_array["$i"] ) {
						$max_calls = $result_array["$i"];
					}
				}
				$tot_calls++;
			}
		}
		catch (PDOException $e) {
			print $e->getMessage();
		}
		$sth = null;
		for ( $i=$start_timestamp; $i<=$end_timestamp; ++$i ) {
			$group_by_str = substr(strftime($group_by_field_php[0],$i),0,$group_by_field_php[1]) . $group_by_field_php[2];
			if ( ! isset($result_array_cc[ "$group_by_str" ]) || ( isset($result_array["$i"]) && $result_array_cc[ "$group_by_str" ][1] < $result_array["$i"] ) ) {
				$result_array_cc[ "$group_by_str" ][0] = $i;
				$result_array_cc[ "$group_by_str" ][1] = isset($result_array["$i"]) ? $result_array["$i"] : 0;
			}
		}
	}
	if ( $tot_calls ) {
		echo '<p class="center title">Параллельные звонки по '.$graph_col_title.'</p><table class="cdr">
		<tr>
			<th class="end_col">'. $graph_col_title . '</th>
			<th class="center_col">Всего звонков: '. $tot_calls .' | Максимум звонков: '. $max_calls .'</th>
			<th class="end_col">Дата звонка</th>
		</tr>';
	
		ksort($result_array_cc);

		foreach ( array_keys($result_array_cc) as $group_by_key ) {
			$full_time = strftime( '%Y-%m-%d %H:%M:%S', $result_array_cc[ "$group_by_key" ][0] );
			$group_by_cur = $result_array_cc[ "$group_by_key" ][1];
			$bar_calls = $group_by_cur/$max_calls*100;
			echo '<tr>';
			echo "<td class=\"end_col\">$group_by_key</td><td class=\"center_col\"><div class=\"bar_calls\" style=\"width : $bar_calls%\">&nbsp;$group_by_cur</div></td><td class=\"end_col\">$full_time</td>";
			echo '</tr>';
		}

		echo '</table>';
	}
}

if ( isset($_REQUEST['need_asr_report']) && $_REQUEST['need_asr_report'] == 'true' ) {
	$query2 = "SELECT $group_by_field AS group_by_field, disposition, count(*) AS total_calls, sum(billsec) AS total_duration FROM $db_table_name $where GROUP BY group_by_field,disposition ORDER BY group_by_field ASC LIMIT $result_limit";

	$tot_calls = 0;
	$tot_duration = 0;

	echo '<p class="center title">ASR и ACD по '.$graph_col_title.'</p><table class="cdr">
		<tr>
			<th class="end_col">'. $graph_col_title . '</th>
			<th class="end_col">ASR</th>
			<th class="end_col">ACD</th>
			<th class="end_col">Всего звонков</th>
			<th class="end_col">Отвеченных звонков</th>
			<th class="end_col">Секунд по биллингу</th>
		</tr>';

	$asr_cur_key = '';
	$asr_answered_calls = 0;
	$asr_total_calls = 0;
	$asr_bill_secs = 0;

	$all_asr_answered_calls = 0;
	$all_asr_total_calls = 0;
	$all_asr_bill_secs = 0;
	
	try {
		$sth = $dbh->query($query2);
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
		while ($row = $sth->fetch(PDO::FETCH_NUM)) {
			if ( $asr_cur_key != '' && $row[0] != $asr_cur_key ) {
				echo '<tr  class="record">';
				echo "<td class=\"end_col\">$asr_cur_key</td><td class=\"chart_data\">",intval(($asr_answered_calls/$asr_total_calls)*100),"</td><td class=\"chart_data\">",intval($asr_bill_secs/($asr_answered_calls?$asr_answered_calls:1)),"</td><td class=\"chart_data\">$asr_total_calls</td><td class=\"chart_data\">$asr_answered_calls</td><td class=\"chart_data\">$asr_bill_secs</td>";
				echo '</tr>';
				$asr_answered_calls = $asr_total_calls = $asr_bill_secs = 0;
			}
			$asr_total_calls += $row[2];
			$asr_bill_secs += $row[3];
			
			$all_asr_total_calls += $row[2];
			$all_asr_bill_secs += $row[3];
			
			if ( $row[1] == 'ANSWERED' ) {
				$asr_answered_calls += $row[2];
				$all_asr_answered_calls += $row[2]; 
			}
			$asr_cur_key = $row[0];
		}
	}
	catch (PDOException $e) {
		print $e->getMessage();
	}
	$sth = null;

	if ( $asr_cur_key != '' ) {
		echo '<tr class="record">';
		echo "<td class=\"end_col\">$asr_cur_key</td><td class=\"chart_data\">",($asr_total_calls ? intval(($asr_answered_calls/$asr_total_calls)*100) : 0),"</td><td class=\"chart_data\">",intval($asr_bill_secs/($asr_answered_calls?$asr_answered_calls:1)),"</td><td class=\"chart_data\">$asr_total_calls</td><td class=\"chart_data\">$asr_answered_calls</td><td class=\"chart_data\">$asr_bill_secs</td>";
		echo '</tr>';
	}

	echo '<tr>';
	echo "<th class=\"chart_data\">Всего</th><th class=\"chart_data\">",($all_asr_total_calls ? intval(($all_asr_answered_calls/$all_asr_total_calls)*100) : 0),"</th><th class=\"chart_data\">",intval($all_asr_bill_secs/($all_asr_answered_calls?$all_asr_answered_calls:1)),"</th><th class=\"chart_data\">$all_asr_total_calls</th><th class=\"chart_data\">$all_asr_answered_calls</th><th class=\"chart_data\">$all_asr_bill_secs</th>";
	echo '</tr>';
	echo '</table>';

}

# Запуск плагинов
if ( Config::exists('system.plugins') && Config::get('system.plugins') ) {
	foreach ( Config::get('system.plugins') as $p_val ) {
		if ( !empty($_REQUEST['need_'.$p_val]) && $_REQUEST['need_'.$p_val] == 'true' ) { 
			eval( $p_val . '();' );
		}
	}
}

$dbh = null;

if ( !isset($_POST['form_submitted']) ) {
	require_once 'templates/footer.php';
}

//echo 'Page time: ' . ( microtime(true) - $start_timer ) . ' sec.';
