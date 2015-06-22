<!doctype html>
<html>
<head>
<title><?php echo $site_title;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="<?php echo $site_desc;?>">
<meta name="robots" content="<?php echo $site_robots;?>">
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="address=no">
<link rel="icon" type="image/png" sizes="192x192" href="img/favicons/favicon-192x192.png">
<link rel="apple-touch-icon" sizes="192x192" href="img/favicons/favicon-192x192.png">
<meta name="msapplication-config" content="browserconfig.xml">
<link rel="stylesheet" type="text/css" href="img/style.css?<?php echo filemtime('img/style.css');?>">
<link rel="stylesheet" type="text/css" href="<?php echo isset($cdn_addr) ? $cdn_css_tooltip : 'img/simptip.css'; ?>">
<script src="img/script.js?<?=filemtime('img/script.js');?>"></script>
</head>
<body>
<div id="container">
	<table id="header">
		<tr>
			<td id="header_title" colspan="2">
				<span><a title="Перейти в основной раздел" href="<?php echo $site_gen_section;?>">&#8592;</a></span>
				<a href="."><?php echo $site_head;?></a>
			</td>
		</tr>
		<tr>
			<td class="sub" align='right'>
				<?php
				if (strlen(getenv('REMOTE_USER'))) {
					echo '<a href="index.php?action=logout">выйти: '.getenv('REMOTE_USER').'</a>';
				}
				?>
			</td>
		</tr>
		</table>
		<div onclick="hideRecord();" id="playerBox"></div>
