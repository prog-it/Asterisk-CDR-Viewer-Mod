<div id="main">
<table class="cdr cdr-main">
<tr>
<td>

<form method="post" enctype="application/x-www-form-urlencoded" action="">
<fieldset>
<legend class="title">Просмотр записей о совершенных звонках</legend>
<table width="100%">
<tr>
	<th>Сортировать по</th>
	<th>Условия поиска</th>
	<th>&nbsp;</th>
</tr>
<tr>
<td><input <?php if (empty($_REQUEST['order']) || $_REQUEST['order'] == 'calldate') { echo 'checked="checked"'; } ?> id="id_order_calldate" type="radio" name="order" value="calldate">&nbsp;<label for="id_order_calldate">Дата</label></td>
<td>С&nbsp;
<select name="startday" id="startday">
	<?php
	for ($i = 1; $i <= 31; $i++) {
		if ( (is_blank($_REQUEST['startday']) && date('d', time()) == $i) || (isset($_REQUEST['startday']) && $i == $_REQUEST['startday']) ) {
			echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		} else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	?>
</select>
<select name="startmonth" id="startmonth">
<?php
$months = array('01' => 'Январь', '02' => 'Февраль', '03' => 'Март', '04' => 'Апрель', '05' => 'Май', '06' => 'Июнь', '07' => 'Июль', '08' => 'Август', '09' => 'Сентябрь', '10' => 'Октябрь', '11' => 'Ноябрь', '12' => 'Декабрь');
foreach ($months as $i => $month) {
	if ((is_blank($_REQUEST['startmonth']) && date('m') == $i) || (isset($_REQUEST['startmonth']) && $_REQUEST['startmonth'] == $i)) {
		echo '<option value="'.$i.'" selected="selected">'.$month.'</option>';
	} else {
		echo '<option value="'.$i.'">'.$month.'</option>';
	}
}
?>
</select>
<select name="startyear" id="startyear">
	<?php
	for ( $i = 2000; $i <= date('Y'); $i++) {
		if ((empty($_REQUEST['startyear']) && date('Y') == $i) || (isset($_REQUEST['startyear']) && $_REQUEST['startyear'] == $i)) {
			echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		} else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	?>
</select>&nbsp;
<select name="starthour" id="starthour">
	<?php
	for ($i = 0; $i <= 23; $i++) {
		if ( isset($_REQUEST['starthour']) && $_REQUEST['starthour'] == $i ) {
			echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		} else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	?>
</select>
:
<input type="tel" name="startmin" id="startmin" list="list_startmin" pattern="[0-9]{0,2}" maxlength="2" size="2" autocomplete="off" value="<?php if (isset($_REQUEST['startmin'])) { echo htmlspecialchars($_REQUEST['startmin']); } else { echo '00'; } ?>">&ensp;
<datalist id="list_startmin">
	<option value="00"></option>
	<option value="30"></option>
</datalist>
По&ensp;
<select name="endday" id="endday">
	<?php
	for ($i = 1; $i <= 31; $i++) {
		if ( (is_blank($_REQUEST['endday']) && date('d', time()) == $i) || (isset($_REQUEST['endday']) && $i == $_REQUEST['endday']) ) {
			echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		} else {
			echo !isset($_REQUEST['endday']) && $i == 31 ? '<option value="'.$i.'" selected="selected">'.$i.'</option>' : '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	?>
</select>
<select name="endmonth" id="endmonth">
	<?php
	foreach ($months as $i => $month) {
		if ((is_blank($_REQUEST['endmonth']) && date('m') == $i) || (isset($_REQUEST['endmonth']) && $_REQUEST['endmonth'] == $i)) {
			echo '<option value="'.$i.'" selected="selected">'.$month.'</option>';
		} else {
			echo '<option value="'.$i.'">'.$month.'</option>';
		}
	}
	?>
</select>
<select name="endyear" id="endyear">
	<?php
	for ( $i = 2000; $i <= date('Y'); $i++) {
		if ((empty($_REQUEST['endyear']) && date('Y') == $i) || (isset($_REQUEST['endyear']) && $_REQUEST['endyear'] == $i)) {
			echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		} else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	?>
</select>&nbsp;
<select name="endhour" id="endhour">
	<?php
	for ($i = 0; $i <= 23; $i++) {
		if ( isset($_REQUEST['endhour']) && $_REQUEST['endhour'] == $i ) {
			echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
		} else {
			echo !isset($_REQUEST['endhour']) && $i == 23 ? '<option value="'.$i.'" selected="selected">'.$i.'</option>' : '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	?>
</select>
:
<input type="tel" name="endmin" id="endmin" list="list_endmin" pattern="[0-9]{0,2}" maxlength="2" size="2" autocomplete="off" value="<?php if (isset($_REQUEST['endmin'])) { echo htmlspecialchars($_REQUEST['endmin']); } else { echo '59'; } ?>">
<datalist id="list_endmin">
	<option value="00"></option>
	<option value="30"></option>
</datalist>
&emsp;
<select id="id_range" name="range" onchange="selectRange(this.value);">
	<option class="head" value="">Выбрать период...</option>
	<option value="td">Сегодня</option>
	<option value="yd">Вчера</option>
	<option value="3d">Последние 3 дня</option>
	<option value="tw">Текущая неделя</option>
	<option value="pw">Предыдущая неделя</option>
	<option value="3w">Последние 3 недели</option>
	<option value="tm">Текущий месяц</option>
	<option value="pm">Предыдущий месяц</option>
	<option value="3m">Последние 3 месяца</option>
</select>
</td>
<td rowspan="13" valign='top' align='right'>
<fieldset>
<legend class="title">Дополнительные опции</legend>
<table>
<tr>
	<td>Тип отчета&ensp;</td>
	<td>
	<input <?php if ( (empty($_REQUEST['need_html']) && empty($_REQUEST['need_chart']) && empty($_REQUEST['need_chart_cc']) && empty($_REQUEST['need_minutes_report']) && empty($_REQUEST['need_asr_report']) && empty($_REQUEST['need_csv'])) || ( !empty($_REQUEST['need_html']) &&  $_REQUEST['need_html'] == 'true' ) ) { echo 'checked="checked"'; } ?> type="checkbox" id="id_need_html" name="need_html" value="true">&ensp;<label for="id_need_html">Поиск в базе</label><br>
	<?php
	if ( strlen($callrate_csv_file) > 0 ) {
		//echo '&emsp;<input id="id_use_callrates" type="checkbox" name="use_callrates" value="true"';
		//if ( !empty($_REQUEST['use_callrates']) &&  $_REQUEST['use_callrates'] == 'true' ) { echo 'checked="checked"'; }
		//if ( (empty($_REQUEST['need_html']) && empty($_REQUEST['need_chart']) && empty($_REQUEST['need_chart_cc']) && empty($_REQUEST['need_minutes_report']) && empty($_REQUEST['need_asr_report']) && empty($_REQUEST['need_csv'])) || ( !empty($_REQUEST['use_callrates']) &&  $_REQUEST['use_callrates'] == 'true' )  ) { echo 'checked="checked"'; }
		//echo '>&ensp;<label for="id_use_callrates">С тарифами</label><br/>';
		if ( (empty($_REQUEST['need_html']) && empty($_REQUEST['need_chart']) && empty($_REQUEST['need_chart_cc']) && empty($_REQUEST['need_minutes_report']) && empty($_REQUEST['need_asr_report']) && empty($_REQUEST['need_csv'])) || ( !empty($_REQUEST['need_html']) &&  $_REQUEST['need_html'] == 'true' ) ) { $_REQUEST['use_callrates'] = 'true'; }
	} 
	?>
	<input <?php if ( !empty($_REQUEST['need_csv']) && $_REQUEST['need_csv'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" id="id_need_csv" name="need_csv" value="true">&ensp;<label for="id_need_csv">CSV файл</label><br/>
	<input <?php if ( !empty($_REQUEST['need_chart']) && $_REQUEST['need_chart'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" id="id_need_chart" name="need_chart" value="true">&ensp;<label for="id_need_chart">График звонков</label><br>
	<input <?php if ( !empty($_REQUEST['need_minutes_report']) && $_REQUEST['need_minutes_report'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" id="id_need_minutes_report" name="need_minutes_report" value="true">&ensp;<label for="id_need_minutes_report">Расход минут</label><br>
	<? if ($display_search['chart_cc'] == 1) { ?>
	<input <?php if ( !empty($_REQUEST['need_chart_cc']) && $_REQUEST['need_chart_cc'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" id="id_need_chart_cc" name="need_chart_cc" value="true">&ensp;<label for="id_need_chart_cc">Параллельные звонки</label><br>
	<? } ?>
	<? if ($display_search['asr_report'] == 1) { ?>
	<input <?php if ( !empty($_REQUEST['need_asr_report']) && $_REQUEST['need_asr_report'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" id="id_need_asr_report" name="need_asr_report" value="true">&ensp;<label for="id_need_asr_report">ASR и ACD</label><br> 
	<? } ?>
	</td>
</tr>
<?php
if ( isset($plugins) && $plugins && count($plugins) > 0 ) {
	echo '<tr><td label for="Plugins">Плагины&ensp;</td><td><hr>';
	foreach ( $plugins as $p_key => $p_val ) {
		echo '<input id="id_need_'.$p_val.'" type="checkbox" name="need_'.$p_val.'" value="true" ';
		if ( !empty($_REQUEST['need_'.$p_val]) && $_REQUEST['need_'.$p_val] == 'true' ) { 
			echo 'checked="checked"'; 
		}
		echo '>&ensp;<label for="id_need_'.$p_val.'">'. $p_key .'</label><br>';
	}
	echo '</td></tr>';
}
?>
<tr>
	<td>
		<label for="id_result_limit">Кол-во строк</label>&ensp;
	</td>
	<td>
		<hr>
		<input id="id_result_limit" list="list_result_limit" value="<?php 
		if (isset($_REQUEST['limit']) ) { 
			echo htmlspecialchars($_REQUEST['limit']);
		} else {
			echo $db_result_limit;
		} ?>" name="limit" type="number" min="0" step="1" autocomplete="off">
		<datalist id="list_result_limit">
			<option value="10"></option>
			<option value="50"></option>
			<option value="100"></option>
			<option value="500"></option>
			<option value="2000"></option>
		</datalist>
	</td>
</tr>
</table>
</fieldset>
</td>
</tr>

<? if ($display_search['channel'] == 1) { ?>
<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'channel') { echo 'checked="checked"'; } ?> type="radio" id="id_order_channel" name="order" value="channel">&nbsp;<label for="id_order_channel">Входящий канал</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="channel" id="channel" value="<?php if (isset($_REQUEST['channel'])) { echo htmlspecialchars($_REQUEST['channel']); } ?>">
		<input <?php if ( isset($_REQUEST['channel_neg'] ) && $_REQUEST['channel_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="channel_neg" value="true" id="id_channel_neg"> <label for="id_channel_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['channel_mod']) || $_REQUEST['channel_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="channel_mod" value="begins_with" id="id_channel_mod1"> <label for="id_channel_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['channel_mod']) && $_REQUEST['channel_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="channel_mod" value="contains" id="id_channel_mod2"> <label for="id_channel_mod2">Содержит</label> &ensp;
		<input <?php if (isset($_REQUEST['channel_mod']) && $_REQUEST['channel_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="channel_mod" value="ends_with" id="id_channel_mod3"> <label for="id_channel_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['channel_mod']) && $_REQUEST['channel_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="channel_mod" value="exact" id="id_channel_mod4"> <label for="id_channel_mod4">Равно</label>
	</td>
</tr>
<? } ?>

<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'src') { echo 'checked="checked"'; } ?> type="radio" id="id_order_src" name="order" value="src">&nbsp;<label for="id_order_src">Кто звонил</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="src" id="src" value="<?php if (isset($_REQUEST['src'])) { echo htmlspecialchars($_REQUEST['src']); } ?>">
		<input <?php if ( isset($_REQUEST['src_neg'] ) && $_REQUEST['src_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="src_neg" value="true" id="id_src_neg"> <label for="id_src_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['src_mod']) || $_REQUEST['src_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="begins_with" id="id_src_mod1"> <label for="id_src_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['src_mod']) && $_REQUEST['src_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="contains" id="id_src_mod2"> <label for="id_src_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['src_mod']) && $_REQUEST['src_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="ends_with" id="id_src_mod3"> <label for="id_src_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['src_mod']) && $_REQUEST['src_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="exact" id="id_src_mod4"> <label for="id_src_mod4">Равно</label> 
	</td>
</tr>

<? if ($display_search['clid'] == 1) { ?>
<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'clid') { echo 'checked="checked"'; } ?> type="radio" id="id_order_clid" name="order" value="clid">&nbsp;<label for="id_order_clid">Имя звонящего</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="clid" id="clid" value="<?php if (isset($_REQUEST['clid'])) { echo htmlspecialchars($_REQUEST['clid']); } ?>">
		<input <?php if ( isset($_REQUEST['clid_neg'] ) && $_REQUEST['clid_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="clid_neg" value="true" id="id_clid_neg"> <label for="id_clid_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['clid_mod']) || $_REQUEST['clid_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="clid_mod" value="begins_with" id="id_clid_mod1"> <label for="id_clid_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['clid_mod']) && $_REQUEST['clid_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="clid_mod" value="contains" id="id_clid_mod2"> <label for="id_clid_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['clid_mod']) && $_REQUEST['clid_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="clid_mod" value="ends_with" id="id_clid_mod3"> <label for="id_clid_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['clid_mod']) && $_REQUEST['clid_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="clid_mod" value="exact" id="id_clid_mod4"> <label for="id_clid_mod4">Равно</label> 
	</td>
</tr>
<? } ?>

<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'dst') { echo 'checked="checked"'; } ?> type="radio" id="id_order_dst" name="order" value="dst">&nbsp;<label for="id_order_dst">Куда звонили</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="dst" id="dst" value="<?php if (isset($_REQUEST['dst'])) { echo htmlspecialchars($_REQUEST['dst']); } ?>">
		<input <?php if ( isset($_REQUEST['dst_neg'] ) &&  $_REQUEST['dst_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="dst_neg" value="true" id="id_dst_neg"> <label for="id_dst_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['dst_mod']) || $_REQUEST['dst_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="begins_with" id="id_dst_mod1"> <label for="id_dst_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['dst_mod']) && $_REQUEST['dst_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="contains" id="id_dst_mod2"> <label for="id_dst_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['dst_mod']) && $_REQUEST['dst_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="ends_with" id="id_dst_mod3"> <label for="id_dst_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['dst_mod']) && $_REQUEST['dst_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="exact" id="id_dst_mod4"> <label for="id_dst_mod4">Равно</label> 
	</td>
</tr>

<? if ($display_search['did'] == 1) { ?>
<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'did') { echo 'checked="checked"'; } ?> type="radio" id="id_order_did" name="order" value="did">&nbsp;<label for="id_order_did">DID (если есть)</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="did" id="did" value="<?php if (isset($_REQUEST['did'])) { echo htmlspecialchars($_REQUEST['did']); } ?>">
		<input <?php if ( isset($_REQUEST['did_neg'] ) &&  $_REQUEST['did_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="did_neg" value="true" id="id_did_neg"> <label for="id_did_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['did_mod']) || $_REQUEST['did_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="begins_with" id="id_did_mod1"> <label for="id_did_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['did_mod']) && $_REQUEST['did_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="contains" id="id_did_mod2"> <label for="id_did_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['did_mod']) && $_REQUEST['did_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="ends_with" id="id_did_mod3"> <label for="id_did_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['did_mod']) && $_REQUEST['did_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="did_mod" value="exact" id="id_did_mod4"> <label for="id_did_mod4">Равно</label> 
	</td>
</tr>
<? } ?>

<? if ($display_search['dstchannel'] == 1) { ?>
<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'dstchannel') { echo 'checked="checked"'; } ?> type="radio" id="id_order_dstchannel" name="order" value="dstchannel">&nbsp;<label for="id_order_dstchannel">Исходящий канал</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="dstchannel" id="dstchannel" value="<?php if (isset($_REQUEST['dstchannel'])) { echo htmlspecialchars($_REQUEST['dstchannel']); } ?>">
		<input <?php if ( isset($_REQUEST['dstchannel_neg'] ) &&  $_REQUEST['dstchannel_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="dstchannel_neg" value="true" id="id_dstchannel_neg"> <label for="id_dstchannel_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['dstchannel_mod']) || $_REQUEST['dstchannel_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="dstchannel_mod" value="begins_with" id="id_dstchannel_mod1"> <label for="id_dstchannel_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['dstchannel_mod']) && $_REQUEST['dstchannel_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="dstchannel_mod" value="contains" id="id_dstchannel_mod2"> <label for="id_dstchannel_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['dstchannel_mod']) && $_REQUEST['dstchannel_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="dstchannel_mod" value="ends_with" id="id_dstchannel_mod3"> <label for="id_dstchannel_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['dstchannel_mod']) && $_REQUEST['dstchannel_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="dstchannel_mod" value="exact" id="id_dstchannel_mod4"> <label for="id_dstchannel_mod4">Равно</label> 
	</td>
</tr>
<? } ?>

<? if ($display_search['accountcode'] == 1) { ?>
<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'accountcode') { echo 'checked="checked"'; } ?> type="radio" id="id_order_accountcode" name="order" value="accountcode">&nbsp;<label for="id_order_accountcode">Код аккаунта</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="accountcode" id="accountcode" value="<?php if (isset($_REQUEST['accountcode'])) { echo htmlspecialchars($_REQUEST['accountcode']); } ?>">
		<input <?php if ( isset($_REQUEST['accountcode_neg'] ) &&  $_REQUEST['accountcode_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="accountcode_neg" value="true" id="id_accountcode_neg"> <label for="id_accountcode_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['accountcode_mod']) || $_REQUEST['accountcode_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="begins_with" id="id_accountcode_mod1"> <label for="id_accountcode_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['accountcode_mod']) && $_REQUEST['accountcode_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="contains" id="id_accountcode_mod2"> <label for="id_accountcode_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['accountcode_mod']) && $_REQUEST['accountcode_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="ends_with" id="id_accountcode_mod3"> <label for="id_accountcode_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['accountcode_mod']) && $_REQUEST['accountcode_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="accountcode_mod" value="exact" id="id_accountcode_mod4"> <label for="id_accountcode_mod4">Равно</label> 
	</td>
</tr>
<? } ?>

<? if ($display_search['userfield'] == 1) { ?>
<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'userfield') { echo 'checked="checked"'; } ?> type="radio" id="id_order_userfield" name="order" value="userfield">&nbsp;<label for="id_order_userfield">Описание</label>
	</td>
	<td>
		<input class="margin-left0" type="text" name="userfield" id="userfield" value="<?php if (isset($_REQUEST['userfield'])) { echo htmlspecialchars($_REQUEST['userfield']); } ?>">
		<input <?php if ( isset($_REQUEST['userfield_neg'] ) &&  $_REQUEST['userfield_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="userfield_neg" value="true" id="id_userfield_neg"> <label for="id_userfield_neg">Не</label> &ensp;
		<input <?php if (empty($_REQUEST['userfield_mod']) || $_REQUEST['userfield_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="begins_with" id="id_userfield_mod1"> <label for="id_userfield_mod1">Начинается с</label> &ensp;
		<input <?php if (isset($_REQUEST['userfield_mod']) && $_REQUEST['userfield_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="contains" id="id_userfield_mod2"> <label for="id_userfield_mod2">Содержит</label> &ensp; 
		<input <?php if (isset($_REQUEST['userfield_mod']) && $_REQUEST['userfield_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="ends_with" id="id_userfield_mod3"> <label for="id_userfield_mod3">Заканчивается на</label> &ensp;
		<input <?php if (isset($_REQUEST['userfield_mod']) && $_REQUEST['userfield_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="userfield_mod" value="exact" id="id_userfield_mod4"> <label for="id_userfield_mod4">Равно</label> 
	</td>
</tr>
<? } ?>

<tr>
	<td>
		<input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'duration') { echo 'checked="checked"'; } ?> type="radio" id="id_order_duration" name="order" value="duration">&nbsp;<label for="id_order_duration">Длительность</label>
	</td>
	<td>
		<input type="number" min="0" step="1" name="dur_min" id="id_dur_min" value="<?php if (isset($_REQUEST['dur_min'])) { echo htmlspecialchars($_REQUEST['dur_min']); } ?>" placeholder="от">
		&ensp;&ndash;&ensp;
		<input type="number" min="0" step="1" name="dur_max" id="id_dur_max" value="<?php if (isset($_REQUEST['dur_max'])) { echo htmlspecialchars($_REQUEST['dur_max']); } ?>" placeholder="до">
		&nbsp;<label for="id_dur_max">сек.</label>
	</td>
</tr>

<? if ($display_search['lastapp'] == 1) { ?>
<tr>
	<td><input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'lastapp') { echo 'checked="checked"'; } ?> type="radio" id="id_order_lastapp" name="order" value="lastapp">&nbsp;<label for="id_order_lastapp">Приложение</label></td>
	<td>
		<input <?php if ( isset($_REQUEST['lastapp_neg'] ) && $_REQUEST['lastapp_neg'] == 'true' ) { echo 'checked="checked"'; } ?> class="margin-left0" type="checkbox" name="lastapp_neg" id="id_lastapp_neg" value="true"> <label for="id_lastapp_neg">Не</label>&nbsp;
		<select name="lastapp" id="lastapp">
			<option <?php if (empty($_REQUEST['lastapp']) || $_REQUEST['lastapp'] == 'all') { echo 'selected="selected"'; } ?> value="all">Любое</option>
			<option <?php if (isset($_REQUEST['lastapp']) && $_REQUEST['lastapp'] == 'Dial') { echo 'selected="selected"'; } ?> value="Dial">Набор номера</option>
			<option <?php if (isset($_REQUEST['lastapp']) && $_REQUEST['lastapp'] == 'Hangup') { echo 'selected="selected"'; } ?> value="Hangup">Разъединение</option>
			<option <?php if (isset($_REQUEST['lastapp']) && $_REQUEST['lastapp'] == 'Playback') { echo 'selected="selected"'; } ?> value="Playback">Воспроизведение</option>
			<option <?php if (isset($_REQUEST['lastapp']) && $_REQUEST['lastapp'] == 'RetryDial') { echo 'selected="selected"'; } ?> value="RetryDial">Повторный набор</option>
			<option <?php if (isset($_REQUEST['lastapp']) && $_REQUEST['lastapp'] == 'Queue') { echo 'selected="selected"'; } ?> value="Queue">Очередь</option>
		</select>
	</td>
</tr>
<? } ?>

<tr>
	<td><input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'disposition') { echo 'checked="checked"'; } ?> type="radio" id="id_order_disposition" name="order" value="disposition">&nbsp;<label for="id_order_disposition">Статус звонка</label></td>
	<td>
		<input <?php if ( isset($_REQUEST['disposition_neg'] ) && $_REQUEST['disposition_neg'] == 'true' ) { echo 'checked="checked"'; } ?> class="margin-left0" type="checkbox" name="disposition_neg" id="id_disposition_neg" value="true"> <label for="id_disposition_neg">Не</label>&nbsp;
		<select name="disposition" id="disposition">
			<option <?php if (empty($_REQUEST['disposition']) || $_REQUEST['disposition'] == 'all') { echo 'selected="selected"'; } ?> value="all">Любой</option>
			<option <?php if (isset($_REQUEST['disposition']) && $_REQUEST['disposition'] == 'ANSWERED') { echo 'selected="selected"'; } ?> value="ANSWERED">Отвечено</option>
			<option <?php if (isset($_REQUEST['disposition']) && $_REQUEST['disposition'] == 'NO ANSWER') { echo 'selected="selected"'; } ?> value="NO ANSWER">Не отвечено</option>
			<option <?php if (isset($_REQUEST['disposition']) && $_REQUEST['disposition'] == 'BUSY') { echo 'selected="selected"'; } ?> value="BUSY">Занято</option>
			<option <?php if (isset($_REQUEST['disposition']) && $_REQUEST['disposition'] == 'FAILED') { echo 'selected="selected"'; } ?> value="FAILED">Ошибка</option>
			<option <?php if (isset($_REQUEST['disposition']) && $_REQUEST['disposition'] == 'CONGESTION') { echo 'selected="selected"'; } ?> value="CONGESTION">Перегрузка</option>
		</select>
	</td>
</tr>
<tr>
	<td>
		<select name="sort" id="sort">
		<option <?php if (isset($_REQUEST['sort']) && $_REQUEST['sort'] == 'ASC') { echo 'selected="selected"'; } ?> value="ASC">по возрастанию</option>
		<option <?php if (empty($_REQUEST['sort']) || $_REQUEST['sort'] == 'DESC') { echo 'selected="selected"'; } ?> value="DESC">по убыванию</option>
		</select>
	</td>
	<td>
		<label for="group">Группировать по</label>&nbsp;
		<select name="group" id="group">
			<optgroup label="Информация об аккаунте">
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'accountcode') { echo 'selected="selected"'; } ?> value="accountcode">Код аккаунта</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'userfield') { echo 'selected="selected"'; } ?> value="userfield">Описание</option>
			</optgroup>
			<optgroup label="Дата / Время">
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'minutes1') { echo 'selected="selected"'; } ?> value="minutes1">Минута</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'minutes10') { echo 'selected="selected"'; } ?> value="minutes10">10 минут</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'hour') { echo 'selected="selected"'; } ?> value="hour">Час</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'hour_of_day') { echo 'selected="selected"'; } ?> value="hour_of_day">Час дня</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'day_of_week') { echo 'selected="selected"'; } ?> value="day_of_week">День недели</option>
				<option <?php if (empty($_REQUEST['group']) || $_REQUEST['group'] == 'day') { echo 'selected="selected"'; } ?> value="day">День</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'week') { echo 'selected="selected"'; } ?> value="week">Неделя (ПН-ВС)</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'month') { echo 'selected="selected"'; } ?> value="month">Месяц</option>
			</optgroup>
			<optgroup label="Номер телефона">
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'clid') { echo 'selected="selected"'; } ?> value="clid">Имя звонящего</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'src') { echo 'selected="selected"'; } ?> value="src">Кто звонил</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'did') { echo 'selected="selected"'; } ?> value="dst">DID</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'dst') { echo 'selected="selected"'; } ?> value="dst">Куда звонили</option>
			</optgroup>
			<optgroup label="Тех. информация">
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'disposition') { echo 'selected="selected"'; } ?> value="disposition">Статус</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'disposition_by_day') { echo 'selected="selected"'; } ?> value="disposition_by_day">Статус по дням</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'disposition_by_hour') { echo 'selected="selected"'; } ?> value="disposition_by_hour">Статус по часам</option>
				<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'dcontext') { echo 'selected="selected"'; } ?> value="dcontext">Контекст</option>
			</optgroup>
		</select>
	</td>
</tr>
<tr>
	<td>
	&nbsp;
	</td>
	<td>
		<input class="submit btnSearch margin-left0" type="submit" value="Найти">
		<input <?php if (empty($_REQUEST['search_mode']) || $_REQUEST['search_mode'] == 'all') { echo 'checked="checked"'; } ?> type="radio" id="id_search_mode_all" name="search_mode" value="all"> <label for="id_search_mode_all">По всем условиям</label>&ensp;
		<input <?php if (isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any') { echo 'checked="checked"'; } ?> type="radio" id="id_search_mode_any" name="search_mode" value="any"> <label for="id_search_mode_any">По любому из условий</label>
	</td>
</tr>
</table>
</fieldset>
</form>
</td>
</tr>
</table>
<a id="CDR"></a>

