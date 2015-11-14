<?php //error_reporting(E_ALL | E_STRICT); ini_set('display_errors', 'On');

require_once 'inc/config.inc.php';
require_once 'inc/func.inc.php';

require_once 'templates/header.tpl.php';
require_once 'templates/form.tpl.php';


try {
	$dbh = new PDO("$db_type:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, $db_options);
}
catch (PDOException $e) {
	echo "\nPDO::errorInfo():\n";
	print $e->getMessage();
}

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

#
# asterisk regexp2sqllike
#
if ( is_blank($_REQUEST['src']) ) {
	$src_number = NULL;
} else {
	$src_number = asteriskregexp2sqllike( 'src', '' );
}

if ( is_blank($_REQUEST['dst']) ) {
	$dst_number = NULL;
} else {
	$dst_number = asteriskregexp2sqllike( 'dst', '' );
}

if ( is_blank($_REQUEST['did']) ) {
	$did_number = NULL;
} else {
	$did_number = asteriskregexp2sqllike( 'did', '' );
}

$date_range = "calldate BETWEEN $startdate AND $enddate";
$mod_vars['channel'][] = is_blank($_REQUEST['channel']) ? NULL : $_REQUEST['channel'];
$mod_vars['channel'][] = empty($_REQUEST['channel_mod']) ? NULL : $_REQUEST['channel_mod'];
$mod_vars['channel'][] = empty($_REQUEST['channel_neg']) ? NULL : $_REQUEST['channel_neg'];
$mod_vars['src'][] = $src_number;
$mod_vars['src'][] = empty($_REQUEST['src_mod']) ? NULL : $_REQUEST['src_mod'];
$mod_vars['src'][] = empty($_REQUEST['src_neg']) ? NULL : $_REQUEST['src_neg'];
$mod_vars['clid'][] = is_blank($_REQUEST['clid']) ? NULL : $_REQUEST['clid'];
$mod_vars['clid'][] = empty($_REQUEST['clid_mod']) ? NULL : $_REQUEST['clid_mod'];
$mod_vars['clid'][] = empty($_REQUEST['clid_neg']) ? NULL : $_REQUEST['clid_neg'];
$mod_vars['dstchannel'][] = is_blank($_REQUEST['dstchannel']) ? NULL : $_REQUEST['dstchannel'];
$mod_vars['dstchannel'][] = empty($_REQUEST['dstchannel_mod']) ? NULL : $_REQUEST['dstchannel_mod'];
$mod_vars['dstchannel'][] = empty($_REQUEST['dstchannel_neg']) ? NULL : $_REQUEST['dstchannel_neg'];
$mod_vars['dst'][] = $dst_number;
$mod_vars['dst'][] = empty($_REQUEST['dst_mod']) ? NULL : $_REQUEST['dst_mod'];
$mod_vars['dst'][] = empty($_REQUEST['dst_neg']) ? NULL : $_REQUEST['dst_neg'];
$mod_vars['did'][] = $did_number;
$mod_vars['did'][] = empty($_REQUEST['did_mod']) ? NULL : $_REQUEST['did_mod'];
$mod_vars['did'][] = empty($_REQUEST['did_neg']) ? NULL : $_REQUEST['did_neg'];
$mod_vars['userfield'][] = is_blank($_REQUEST['userfield']) ? NULL : $_REQUEST['userfield'];
$mod_vars['userfield'][] = empty($_REQUEST['userfield_mod']) ? NULL : $_REQUEST['userfield_mod'];
$mod_vars['userfield'][] = empty($_REQUEST['userfield_neg']) ? NULL : $_REQUEST['userfield_neg'];
$mod_vars['accountcode'][] = is_blank($_REQUEST['accountcode']) ? NULL : $_REQUEST['accountcode'];
$mod_vars['accountcode'][] = empty($_REQUEST['accountcode_mod']) ? NULL : $_REQUEST['accountcode_mod'];
$mod_vars['accountcode'][] = empty($_REQUEST['accountcode_neg']) ? NULL : $_REQUEST['accountcode_neg'];
$result_limit = is_blank($_REQUEST['limit']) ? $db_result_limit : intval($_REQUEST['limit']);

if ( strlen($cdr_user_name) > 0 ) {
	$cdr_user_name = asteriskregexp2sqllike( 'cdr_user_name', substr($dbh->quote($cdr_user_name),1,-1) );
	if ( isset($mod_vars['cdr_user_name']) and $mod_vars['cdr_user_name'][2] == 'asterisk-regexp' ) {
		$cdr_user_name = " AND ( dst RLIKE '$cdr_user_name' or src RLIKE '$cdr_user_name' )";
	} else {
		$cdr_user_name = " AND ( dst = '$cdr_user_name' or src = '$cdr_user_name' )";
	}
}

$search_condition = '';

// Build the "WHERE" part of the query

foreach ($mod_vars as $key => $val) {
	if (is_blank($val[0])) {
		unset($_REQUEST[$key.'_mod']);
		$$key = NULL;
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
	$disposition = (empty($_REQUEST['disposition']) || $_REQUEST['disposition'] == 'all') ? NULL : "$search_condition disposition != '$_REQUEST[disposition]'";
} else {
	$disposition = (empty($_REQUEST['disposition']) || $_REQUEST['disposition'] == 'all') ? NULL : "$search_condition disposition = '$_REQUEST[disposition]'";
}

if ( $search_condition == '' ) {
	if ( isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any' ) {
		$search_condition = ' OR ';
	} else {
		$search_condition = ' AND ';
	}
}

$where = "$channel $src $clid $did $dstchannel $dst $userfield $accountcode $disposition";

$duration = (!isset($_REQUEST['dur_min']) || is_blank($_REQUEST['dur_max'])) ? NULL : "duration BETWEEN '$_REQUEST[dur_min]' AND '$_REQUEST[dur_max]'";

if ( strlen($duration) > 0 ) {
	if ( strlen($where) > 8 ) {
		$where = "$where $search_condition $duration";
	} else {
		$where = "$where $duration";
	}
}

$billsec = (!isset($_REQUEST['bill_min']) || is_blank($_REQUEST['bill_max'])) ? NULL : "billsec BETWEEN '$_REQUEST[bill_min]' AND '$_REQUEST[bill_max]'";

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

$order = empty($_REQUEST['order']) ? 'ORDER BY calldate' : "ORDER BY $_REQUEST[order]";
$sort = empty($_REQUEST['sort']) ? 'DESC' : $_REQUEST['sort'];
$group = empty($_REQUEST['group']) ? 'day' : $_REQUEST['group'];

// CSV - разделители -> ;
if ( isset($_REQUEST['need_csv']) && $_REQUEST['need_csv'] == 'true' ) {
	$csv_date = time();
	$csv_file = 'report_' . date('Y-m-d_H-i-s', $csv_date) . '_' . md5($csv_date.'-'.$where) . '.csv';
	//$csv_file = md5(time().'-'.$where).'.csv';
	if (!file_exists("$system_tmp_dir/$csv_file")) {
		$handle = fopen("$system_tmp_dir/$csv_file", "w");
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

		fwrite($handle,"calldate;clid;src;did;dst;dcontext;channel;dstchannel;lastapp;lastdata;duration;billsec;disposition;amaflags;accountcode;uniqueid;userfield");
		
		if ( isset($_REQUEST['use_callrates']) && $_REQUEST['use_callrates'] == 'true' ) {
			fwrite($handle,";callrate;callrate_dst");
		}
		fwrite($handle,"\n");
		
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$csv_line[0] 	= $row['calldate'];
			$csv_line[1] 	= $row['clid'];
			$csv_line[2] 	= $row['src'];
			$csv_line[3] 	= $row['did'];
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
			$csv_line[15]	= $row['uniqueid'];
			$csv_line[16]	= $row['userfield'];
			$data = '';
			if ( isset($_REQUEST['use_callrates']) && $_REQUEST['use_callrates'] == 'true' ) {
				$rates = callrates($row['dst'],$row['billsec'],$callrate_csv_file);
				$csv_line[17] = $rates[4];
				$csv_line[18] = $rates[2];
			}
			for ($i = 0; $i < count($csv_line); $i++) {
				$csv_line[$i] = str_replace( array( "\n", "\r" ), '', $csv_line[$i]);
				/* If the string contains a comma, enclose it in double-quotes. */
				if (strpos($csv_line[$i], ";") !== FALSE) { 	// ,
					$csv_line[$i] = "\"" . $csv_line[$i] . "\"";
				}
				if ($i != count($csv_line) - 1) {
					$data = $data . $csv_line[$i] . ";";
				} else {
					$data = $data . $csv_line[$i];
				}
			}
			unset($csv_line);
			fwrite($handle,"$data\n");
		}
		fclose($handle);
		$sth = NULL;
	}
	echo '<p class="dl_csv"><a class="btn_a_2" href="dl.php?csv='.base64_encode($csv_file).'">Скачать CSV файл</a></p>';
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
		$sth = NULL;
	}
	if ( $tot_calls_raw ) {

		if ( $tot_calls_raw > $result_limit ) {
			echo '<p class="center title">Детализация звонков - показаны '. $result_limit .' из '. $tot_calls_raw .' звонков </p><table class="cdr">';
		} else {
			echo '<p class="center title">Детализация звонков - найдено '. $tot_calls_raw .' звонков </p><table class="cdr">';
		}

		$i = $h_step - 1;

		try {
		
		$query = "SELECT *, unix_timestamp(calldate) as call_timestamp FROM $db_table_name $where $order $sort LIMIT $result_limit";
		$sth = $dbh->query($query);
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			++$i;
			if ($i == $h_step) {
			?>
				<tr>
				<th class="record_col">Дата</th>
				<th class="record_col">Статус</th>
				<th class="record_col">Номер звонящего</th>
				<th class="record_col">Номер назначения</th>
				<?php
					if ( isset($display_column['extension']) and $display_column['extension'] == 1 ) {
						echo '<th class="record_col">Экстеншен</th>';
					}
				?>
				<th class="record_col">Продолжительность</th>
				<?php
				if ( isset($_REQUEST['use_callrates']) && $_REQUEST['use_callrates'] == 'true' ) {
					echo '<th class="record_col">Тариф</th>';
					// Показать Направление
					if ( isset($display_column['callrates_dst']) and $display_column['callrates_dst'] == 1 ) {
						echo '<th class="record_col">Направление</th>';
					}
				}
				
				?>				
				<th class="record_col">Приложение</th>
				<th class="record_col">Вх. канал</th>
				<?php
					if ( isset($display_column['clid']) and $display_column['clid'] == 1 ) {
						echo '<th class="record_col">CallerID</th>';
					}
				?>
				<th class="record_col">Исх. канал</th> 
				<th class="record_col">Файл</th>
				<?php
					if ( isset($display_column['accountcode']) and $display_column['accountcode'] == 1 ) {
						echo '<th class="record_col">Аккаунт</th>';
					}
				?>
				<th class="record_col">Описание</th>
				</tr>
				<?php
				$i = 0;
			}
			echo '<tr class="record">';
			formatCallDate($row['calldate'],$row['uniqueid']);
			formatDisposition($row['disposition'], $row['amaflags']);
			formatSrc($row['src'],$row['clid']);
			if ( isset($row['did']) and strlen($row['did']) ) {
				formatDst($row['did'], $row['dcontext'] . ' # ' . $row['dst'] );
			} else {
				formatDst($row['dst'], $row['dcontext'] );
			}
			if ( isset($display_column['extension']) and $display_column['extension'] == 1 ) {
				formatDst($row['dst'], $row['dcontext'] );
			}
			formatDuration($row['duration'], $row['billsec']);
			if ( isset($_REQUEST['use_callrates']) && $_REQUEST['use_callrates'] == 'true' ) {
				$rates = callrates($row['dst'],$row['billsec'],$callrate_csv_file);
				formatMoney($rates[4],2,htmlspecialchars($rates[2]));
				if ( isset($display_column['callrates_dst']) and $display_column['callrates_dst'] == 1 ) {
					echo '<td>'. htmlspecialchars($rates[2]) .'</td>';
				}
			}			
			formatApp($row['lastapp'], $row['lastdata']);
			formatChannel($row['channel']);
			if ( isset($display_column['clid']) and $display_column['clid'] == 1 ) {
				formatClid($row['clid']);
			}
			formatChannel($row['dstchannel']);
			formatFiles($row);
			if ( isset($display_column['accountcode']) and $display_column['accountcode'] == 1 ) {
				formatAccountCode($row['accountcode']);
			}
			formatUserField($row['userfield']);
			echo '</tr>';
		}
		}
		catch (PDOException $e) {
			print $e->getMessage();
		}
		echo "</table>";
		$sth = NULL;
	}
}
?>

<?php

echo '<a id="GRAPH"></a>';

//NEW GRAPHS
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
		$graph_col_title = 'Номер назначения';
	break;
	case "did":
		$graph_col_title = 'DID';
	break;
	case "src":
		$graph_col_title = 'Номер звонящего';
	break;
	case "clid":
		$graph_col_title = 'CallerID';
	break;
	case "userfield":
		$graph_col_title = 'Польз. поле';
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
	$sth = NULL;
	$tot_duration = sprintf('%02d', intval($tot_duration_secs/60)).':'.sprintf('%02d', intval($tot_duration_secs%60));

	if ( $tot_calls ) {
		echo '<p class="center title">Детализация звонков - График по '.$graph_col_title.'</p><table class="cdr">
		<tr>
			<th class="end_col">'. $graph_col_title . '</th>
			<th class="center_col">Всего звонков: '. $tot_calls .' | Максимум звонков: '. $max_calls .' | Общая продолжительность: '. $tot_duration .'</th>
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

	echo '<p class="center title">Детализация звонков - Расход минут по '.$graph_col_title.'</p><table class="cdr">
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
	$sth = NULL;
	
	$html_duration = sprintf('%02d', intval($tot_duration/60)).':'.sprintf('%02d', intval($tot_duration%60));
	$html_duration_avg = sprintf('%02d', intval(($tot_duration/$tot_calls)/60)).':'.sprintf('%02d', intval(($tot_duration/$tot_calls)%60));

	echo '<tr>';
	echo "<th class=\"chart_data\">Всего</th><th class=\"chart_data\">$tot_calls</th><th class=\"chart_data\">$html_duration</th><th class=\"chart_data\">$html_duration_avg</th>";
	echo '</tr>';
	echo '</table>';
}

if ( isset($_REQUEST['need_chart_cc']) && $_REQUEST['need_chart_cc'] == 'true' ) {
	$date_range = "( (calldate BETWEEN $startdate AND $enddate) or (calldate + interval duration second  BETWEEN $startdate AND $enddate) or ( calldate + interval duration second >= $enddate AND calldate <= $startdate ) )";
	$where = "$channel $dstchannel $src $clid $dst $userfield $accountcode $disposition $duration $cdr_user_name";
	if ( strlen($where) > 9 ) {
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
		$sth = NULL;
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
		$sth = NULL;
		for ( $i=$start_timestamp; $i<=$end_timestamp; ++$i ) {
			$group_by_str = substr(strftime($group_by_field_php[0],$i),0,$group_by_field_php[1]) . $group_by_field_php[2];
			if ( ! isset($result_array_cc[ "$group_by_str" ]) || ( isset($result_array["$i"]) && $result_array_cc[ "$group_by_str" ][1] < $result_array["$i"] ) ) {
				$result_array_cc[ "$group_by_str" ][0] = $i;
				$result_array_cc[ "$group_by_str" ][1] = isset($result_array["$i"]) ? $result_array["$i"] : 0;
			}
		}
	}
	if ( $tot_calls ) {
		echo '<p class="center title">Детализация звонков - Параллельные звонки по '.$graph_col_title.'</p><table class="cdr">
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

	echo '<p class="center title">Детализация звонков - ASR и ACD по '.$graph_col_title.'</p><table class="cdr">
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
			if ( $asr_cur_key != '' and $row[0] != $asr_cur_key ) {
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
	$sth = NULL;

	if ( $asr_cur_key != '' ) {
		echo '<tr class="record">';
		echo "<td class=\"end_col\">$asr_cur_key</td><td class=\"chart_data\">",intval(($asr_answered_calls/$asr_total_calls)*100),"</td><td class=\"chart_data\">",intval($asr_bill_secs/($asr_answered_calls?$asr_answered_calls:1)),"</td><td class=\"chart_data\">$asr_total_calls</td><td class=\"chart_data\">$asr_answered_calls</td><td class=\"chart_data\">$asr_bill_secs</td>";
		echo '</tr>';
	}

	echo '<tr>';
	echo "<th class=\"chart_data\">Всего</th><th class=\"chart_data\">",intval(($all_asr_answered_calls/$all_asr_total_calls)*100),"</th><th class=\"chart_data\">",intval($all_asr_bill_secs/($all_asr_answered_calls?$all_asr_answered_calls:1)),"</th><th class=\"chart_data\">$all_asr_total_calls</th><th class=\"chart_data\">$all_asr_answered_calls</th><th class=\"chart_data\">$all_asr_bill_secs</th>";
	echo '</tr>';
	echo '</table>';

}

/* run Plugins */
if (isset($plugins) && $plugins) {
	foreach ( $plugins as $p_val ) {
		if ( ! empty($_REQUEST['need_'.$p_val]) && $_REQUEST['need_'.$p_val] == 'true' ) { 
			eval( $p_val . '();' );
		}
	}
}

?>

</div>

<?php

$dbh = NULL;

require_once 'templates/footer.tpl.php';

