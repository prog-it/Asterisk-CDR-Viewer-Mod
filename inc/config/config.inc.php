<?php

### Подключение к базе данных
# Тип базы, который поддерживается PDO. Например: mysql, pgsql
$db_type = 'mysql';
# Хост
$db_host = 'localhost';
# Порт
$db_port = '3306';
# Пользователь
$db_user = 'asterisk';
# Пароль
$db_pass = 'asterisk';
# Имя базы
$db_name = 'asterisk';
# Название таблицы
$db_table_name = 'cdr';
# Доп. опции подключения
// $db_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
$db_options = array();

### Максимальное количество записей для вывода ('LIMIT')
$db_result_limit = 100;

### Количество записей, после которых снова будет показана шапка (Дата, Статус...)
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

## Если др. значение, то все записи хранятся в одной папке "/var/calls"
$system_storage_format = 1;

### Папка, где находятся записи Asterisk. БЕЗ слеша на конце
$system_monitor_dir = '/var/calls';

### Размер файла в Килобайтах, больше которого считается, что файл существует
$system_fsize_exists = 10;

### Папка для временных файлов
$system_tmp_dir = '/tmp';

### Разделитель в CSV файле отчета
# Обычно используется запятая ",". Но по умолчанию в Microsoft Office для русского языка установлен разделитель точка с запятой ";"
$system_csv_delim = ';';

### Формат аудио, в котором записываются записи звонков
# Плеер не воспроизводит WAV в Enternet Explorer. В последних версиях Firefox и Chrome все работает
# Например: mp3, wav
$system_audio_format = 'mp3';

### Отложенная конвертация записей звонков. Полезно для снижения нагрузки на сервер
# В этом режиме Asterisk должен записывать записи звонков в WAV, затем каждый день в 00.01 часов файлы из WAV должны быть конвертированы в MP3 с помощью скрипта (см. в папке docs + Readme.txt).
# Файлы за сегодняшний день хранятся в WAV, за прошедшие дни в MP3. В $system_audio_format должно быть задано: mp3. В базу в поле 'filename' будет записано имя файла с расширением wav (имя_файла.wav)
$system_audio_defconv = false;

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
# Если функционал подсчета тарифов не нужен, закомментировать строчку ниже или задать значение ''. Так будет работать немного быстрее при большом количестве записей в выводе
$callrate_csv_file = dirname(__FILE__) . '/../plugins/' . $callrate_csv_fileName;
$callrate_currency = '';
# Массив путей к файлам с тарифами -> array('/var/tarif1.csv', '/var/tarif2.csv', '/var/tarif3.csv');
$callrate_cache = array();

### URL сервиса информации о номере
# Где "%n" будет заменено на номер телефона
$rev_lookup_url = 'http://zvonki.octo.net/number.aspx/' . '%n';
# Минимальная длина номер, для которого будет подставлен URL с инфо о номере
$rev_min_number_len = 7;

### Включение / Отключение показа условий поиска и типов отчетов
## Если 1 - показать, 0 - скрыть
$display_search = array();
# Показ типа отчета - Параллельные звонки
$display_search['chart_cc'] = 0;
# Показ типа отчета - ASR и ACD (Коэффициент отвеченных вызовов / Средняя продолжительность вызова)
$display_search['asr_report'] = 0;
# Показ условия поиска - Входящий канал
$display_search['channel'] = 0;
# Показ условия поиска - Имя звонящего
$display_search['clid'] = 0;
# Показ условия поиска - DID (Внешний номер)
$display_search['did'] = 0;
# Показ условия поиска - Исходящий канал
$display_search['dstchannel'] = 0;
# Показ условия поиска - Код аккаунта
$display_search['accountcode'] = 0;
# Показ условия поиска - Описание (userfield)
$display_search['userfield'] = 0;
# Показ условия поиска - Приложение
$display_search['lastapp'] = 1;
# Удаление дублирующихся записей в Asterisk 13 и выше
$display_search['duphide'] = 1;
# Показ кнопки - Удаление записи звонка
$display_search['rec_delete'] = 0;

### Включение / Отключение показа некоторых колонок
## Если 1 - показать, 0 - скрыть
$display_column = array();
# Показ колонки - CallerID
$display_column['clid'] = 0;
# Показ колонки - Аккаунт
$display_column['accountcode'] = 0;
# Показ колонки - Экстеншен
$display_column['extension'] = 0;
# Показ колонки - Тариф
$display_column['callrates'] = 1;
# Показ колонки - Направление звонка
$display_column['callrates_dst'] = 0;
# Показ колонки - Входящий канал
$display_column['channel'] = 1;
# Показ колонки - Исходящий канал
$display_column['dstchannel'] = 1;
# Показ колонки - Приложение
$display_column['lastapp'] = 1;

### Показать Исх. / Вх. канал полностью
# В колонках Исх. канал и Вх. канал, Например, вместо "SIP" будет показано "SIP/123"
# Если 1 - показать, 0 - скрыть
$display_full_channel = 0;

### CDN
## Если CDN не используется, то закомментировать все строчки $site_cdn
# Основной URL CDN. БЕЗ слеша на конце
//$site_cdn['addr'] = '';
# Tooltips
//$site_cdn['css_tooltip'] = $site_cdn['addr'] . '/simptip/1.0.4/simptip.min.css';
# Плеер
//$site_cdn['js_player'] = $site_cdn['addr'] . '/uppod/0.9.4/uppod.js';
# Скрин для плеера
//$site_cdn['js_player_skin'] = $site_cdn['addr'] . '/uppod/skins/audio_myspace.js';
# jQuery
//$site_cdn['js_jquery'] = $site_cdn['addr'] . '/jquery/1.12.3/jquery.min.js';
# jQuery query object
//$site_cdn['js_jquery_object'] = $site_cdn['addr'] . '/jquery-query-object/2.2.3/jquery.query-object.min.js';

### Настройки сайта
# Meta - Title
$site_main['title'] = 'Детализация звонков';
# Meta - Description
$site_main['desc'] = 'Детализация звонков';
# Meta - Robots
$site_main['robots'] = 'noindex, nofollow';
# Текст в шапке
$site_main['head'] = 'Детализация звонков';
# Путь к изображению с вашим логотипом, которое будет показано в шапке. Вместо текста в $site_main['head']
# Если нужно оставить текст, то закомментировать строчку или задать значение ''
//$site_main['logo_path'] = 'img/example_logo.png';
# Путь к основному разделу сайта
# Чтобы стрелка (рядом с текстом в шапке) не показывалась, закомментировать строчку ниже или задать значение ''
$site_main['main_section'] = '../';
# Автовоспроизведение записи звонка
$site_js['player_autoplay'] = 1;
# Показ даты записи звонка над плеером
$site_js['player_title'] = 1;
# Символ, который будет добавлен в Meta - Title страницы во время воспроизведения записи звонка
$site_js['player_symbol'] = '&#9835;&#9835;&#9835;';
# Показ стрелок для быстрой навигации справа
$site_js['scroll_show'] = 1;

### Имена пользователей, которым разрешен доступ
# $admin_user_names = 'admin1,admin2,admin3';
# Если $admin_user_names = '*'; - разрешено всем
$admin_user_names = '*';



// Имя пользователя
$cdr_user_name = getenv('REMOTE_USER');

if ( strlen($cdr_user_name) > 0 ) {
	$is_admin = strpos(",$admin_user_names,", ",$cdr_user_name,");
	if ( $admin_user_names == '*' ) {
		$cdr_user_name = '';
	} elseif ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout' ) {
		header('Status: 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="CDR Viewer Mod"');
		exit;
	} elseif ( $is_admin !== false ) {
		$cdr_user_name = '';
	}
}

// Загрузка плагинов
if ( isset($plugins) && $plugins ) {
	foreach ( $plugins as $p_val ) {
		require_once "inc/plugins/$p_val.inc.php";
	}
}


