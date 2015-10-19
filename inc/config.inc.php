<?php

### Mysql
$db_type = 'mysql';
$db_host = 'localhost';
$db_port = '3306';
$db_user = 'asterisk';
$db_pass = 'asterisk';
$db_name = 'asterisk';
$db_table_name = 'cdr';
$db_options = array();
// $db_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

### Максимальное количество записей для вывода ('LIMIT')
$db_result_limit = '100';

### Количество записей, после которых снова будет показана шапка (дата статус...)
$h_step = 30;

### Название столбца в БД, в котором хранится название записи звонка
$system_column_name = 'filename';

### Формат хранения файлов записей Asterisk
## Если 1, то файлы записей должны распределяться скриптом по папкам в соответствии с датой "/var/calls/2015/2015-01/2015-01-01". 
# Записи за сегодня находятся в "/var/calls", записи за прошедшие даты в папках в соответствии с датой "/var/calls/2015/2015-01/2015-01-01"

## Если 2, то файлы записей должны распределяться скриптом по папкам в соответствии с датой "/var/calls/2015/12/01".
# Записи за сегодня находятся в "/var/calls", записи за прошедшие даты в папках в соответствии с датой "/var/calls/2015/12/01"

## Если 3, то файлы записей должны распределяться по папкам Asterisk-ом в соответствии с датой "/var/calls/2015/2015-01/2015-01-01".
# Записи за все даты находятся в папках в соответствии с датой "/var/calls/2015/2015-01/2015-01-01"

## Если 4, то файлы записей должны распределяться по папкам Asterisk-ом в соответствии с датой "/var/calls/2015/12/01".
# Записи за все даты находятся в папках в соответствии с датой "/var/calls/2015/12/01"

## Если др. значение, то все записи хранятся в одной папке (/var/calls)
$system_storage_format = 1;

### Папка, где находятся записи Asterisk
$system_monitor_dir = '/var/calls'; // без слеша на конце

### Размер файла в Килобайтах, больше которого считается, что файл существует
$system_fsize_exists = '10';

### Папка для временных файлов
$system_tmp_dir = '/tmp';

### Формат аудио, в котором записываются записи звонков
$system_audio_format = 'mp3';

### Если записи звонков / факсов через некоторое время архивируются, раскомментировать строку ниже и указать формат архива (zip gz rar bz2 и т.д.)
# Имя архива должно быть = имя_файла.mp3.$system_archive_format (имя_файла.mp3.zip)
//$system_archive_format = 'zip';

### Плагины
# Название плагина => имя файла
$plugins = array('Расход средств' => 'my_callrates');

### Тарифы на звонки
# Нетарифицируемый интервал в секундах
$callrate_free_interval = 3;
# Имя файла с тарифами
$callrate_csv_fileName = 'gen_callrates.csv';
# Путь к файлу с тарифами, также этот путь прописан в настройках плагина
$callrate_csv_file = __DIR__ . '/plugins/' . $callrate_csv_fileName;
$callrate_currency = '';
# Массив путей к файлам с тарифами -> array('/var/tarif1.csv', '/var/tarif2.csv', '/var/tarif3.csv');
$callrate_cache = array();

### URL сервиса информации о номере
# Где "%n" будет заменено на номер телефона
//$rev_lookup_url = 'http://zvonki.octo.net/number.aspx/' . '%n';
# Минимальная длина номер, для которого будет подставлен URL с инфо о номере
$rev_min_number_len = 7;

### Включение / Отключение показа некоторых колонок
$display_column = array();
$display_column['clid'] = 0;
$display_column['accountcode'] = 0;
$display_column['extension'] = 0;
# Показ направления звонка
$display_column['callrates_dst'] = 0;

### Показать Исх. / Вх. канал полностью
# В колонках Исх. канал и Вх. канал, Например, вместо "SIP" будет показано "SIP/123"
# Если true - включено, false - отключено
$display_full_channel = false;

### CDN
# Если CDN не используется, то закомментировать $cdn_addr
//$cdn_addr = ''; // без слеша на конце
//$cdn_css_tooltip = $cdn_addr . '/simptip.min.css';

### Настройки сайта
# Meta - Title
$site_title = 'Детализация звонков';
# Meta - Description
$site_desc = 'Детализация звонков';
# Meta - Robots
$site_robots = 'noindex, nofollow';
# Текст в шапке
$site_head = 'Детализация звонков';
# Путь к основному разделу сайта
# Чтобы стрелка (рядом с текстом в шапке) не показывалась, закомментировать строчку ниже или задать значение ''
$site_gen_section = '../';

### Имена пользователей, которым разрешен доступ
# $admin_user_names = 'admin1,admin2,admin3';
# Если $admin_user_names = '*'; - разрешено всем
$admin_user_names = '*';

############################################################# Устарело #############################################################

### Путь к папке где хранятся файлы факсов (.tif)
//$system_fax_archive_dir = '/var/spool/asterisk/fax-gw/archive'; // без слеша на конце

############################################################# / Устарело ############################################################

### Имя пользователя
$cdr_user_name = getenv('REMOTE_USER');

if ( strlen($cdr_user_name) > 0 ) {
	$is_admin = strpos(",$admin_user_names,", ",$cdr_user_name,");
	if ( $admin_user_names == '*' ) {
		$cdr_user_name = '';
	} elseif ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout' ) {
		header('Status: 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="Asterisk"');
		exit;
	} elseif ( $is_admin !== false ) {
		$cdr_user_name = '';
	}
}

/* load Plugins */
if (isset($plugins) && $plugins) {
	foreach ( $plugins as $p_val ) {
		require_once "inc/plugins/$p_val.inc.php";
	}
}


