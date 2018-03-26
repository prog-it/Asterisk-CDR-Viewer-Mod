<!doctype html>
<html>
<head>
<title><?php echo Config::get('site.main.title'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="<?php echo Config::get('site.main.desc'); ?>">
<meta name="robots" content="<?php echo Config::get('site.main.robots'); ?>">
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="address=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#FFFFFF">
<meta name="msapplication-TileColor" content="#BADA55">
<meta name="msapplication-TileImage" content="img/favicon.png">
<meta name="msapplication-config" content="img/browserconfig.xml">
<link rel="manifest" href="img/manifest.json">
<link rel="apple-touch-icon" sizes="192x192" href="img/favicon.png">
<link rel="icon" type="image/png" sizes="192x192" href="img/favicon.png">
<link rel="stylesheet" type="text/css" href="img/style.css?<?php echo filemtime('img/style.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo Config::get('cdn.css.tooltips'); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo Config::get('cdn.css.jquery_contextmenu'); ?>">
<script src="<?php echo Config::get('cdn.js.jquery'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.jquery_object'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.clipboard_js'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.player'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.player_skin'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.jquery_contextmenu'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.jquery_ui_position'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.moment_js'); ?>"></script>
<script src="<?php echo Config::get('cdn.js.moment_js_locale'); ?>"></script>

<script>
	// ID элемента с фоном плеера
var playerOverlayId = '#playerOverlay',
	// ID элемента с плеером
	playerId = '#playerBox',
	// Автовоспроизведение
	playerAutoplay = <?php echo Config::exists('site.js.player_autoplay') && Config::get('site.js.player_autoplay') == 1 ? 'true' : 'false'; ?>,
	// Показ даты записи
	playerTitle = <?php echo Config::exists('site.js.player_title') && Config::get('site.js.player_title') == 1 ? 'true' : 'false'; ?>,
	// Символ, который будет добавлен в Title во время воспроизведения
	playerSymbol = '<?php echo Config::exists('site.js.player_symbol') ? Config::get('site.js.player_symbol') : ''; ?>',
	// Показ стрелок для быстрой навигации справа
	scrollShow = <?php echo Config::exists('site.js.scroll_show') && Config::get('site.js.scroll_show') == 1 ? 'true' : 'false'; ?>,
	// Изменение поля "Комментарий" (userfield)
	userfieldEdit = <?php echo Config::exists('display.main.userfield_edit') && Config::get('display.main.userfield_edit') == 1 ? 'true' : 'false'; ?>,
	// Удаление строки из базы
	entryDelete = <?php echo Config::exists('display.main.entry_delete') && Config::get('display.main.entry_delete') == 1 ? 'true' : 'false'; ?>;
</script>
<script src="img/script.js?<?php echo filemtime('img/script.js'); ?>"></script>
<style>
/* Минимальная и максимальная ширина всего шаблона */
#container {
	min-width: <?php echo Config::get('site.main.min_width') ? Config::get('site.main.min_width') : '1024px'; ?>;	
	max-width: <?php echo Config::get('site.main.max_width') ? Config::get('site.main.max_width') : '1400px'; ?>;
}
</style>
</head>
<body>
<div id="container">
	<table id="header">
		<tr>
			<td id="header_title" colspan="2">
				<?php 
				if ( Config::exists('site.main.main_section') && Config::get('site.main.main_section') != '' ) {
					echo '<span><a title="Перейти в основной раздел" href="' . Config::get('site.main.main_section') . '">&#8592;</a></span>';
				}
				if ( empty($_SERVER['REQUEST_URI']) ){
					$url_cdr = $_SERVER['PHP_SELF'] . ( empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING'] );
				} else {
					$url_cdr = $_SERVER['REQUEST_URI'];
				}				
				if ( Config::exists('site.main.logo_path') && Config::get('site.main.logo_path') != '' ) {
					echo '<a href="' . $url_cdr . '"><img src="' . Config::get('site.main.logo_path') . '"></a>';
				} else {
					echo '<a href="' . $url_cdr . '">' . Config::get('site.main.head') . '</a>';
				}
				?>
			</td>
		</tr>
		<tr>
			<td class="sub" align='right'>
				<?php
				if ( strlen(getenv('REMOTE_USER')) ) {
					echo '<a href="index.php?action=logout">Выйти: '.getenv('REMOTE_USER').'</a>';
				}
				?>
			</td>
		</tr>
		</table>
		<div id="playerOverlay"></div>
		<div id="playerBox"></div>
