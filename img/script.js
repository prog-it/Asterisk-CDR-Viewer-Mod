
$(document).on('ready', function() {
	initClipboard();
	selectRange( $('#id_range').val() );
	
	// Стрелки навигации
	$('#scroll-box').on('click', '#scroll-up', function() {
		$('html, body').animate({ scrollTop: 0 }, 100);
		return false;
	});
	$('#scroll-box').on('click', '#scroll-down', function() {
		$('html, body').animate({ scrollTop: $(document).height() - $(window).height() }, 100);
		return false;
	});
	
	// Быстрый выбор периода
	$('#id_range').on('change', function() {
		 selectRange( $(this).val() );
	});	
	
	// Показать плеер
	$('body').on('click', '.img_play', function() {
		var $player = $(playerId),
			$overlay = $(playerOverlayId),
			autoplay = (playerAutoplay === true) ? 'play' : '',
			docTitle = document.title,
			$title = (playerTitle === true) ? $(this).data('title') : '',
			$config = $.query.get('config'),
			$config = $config != false ? '&config=' + $config : '',
			$link = 'dl.php?f=' + $(this).closest('tr').data('filepath') + $config,
			content = 
				'<div class="plTitle">'+$title+'</div>' +
				'<div class="plStyle" id="player"></div>'
		;
		//link = encodeURIComponent(link);	
		$overlay.css({
			'opacity': 1,
			'visibility': 'visible',
		});
		$player.show();
		$('title').first().html(playerSymbol + ' ' + docTitle);
		$player.html(content);
		this.aplayer = new Uppod({
			m:"audio",
			st:"uppodaudio",
			uid:"player",
			auto:autoplay,
			file:$link,
		});
	});
	
	// Скрыть плеер
	$('body').on('click', '#playerOverlay', function() {
		var $player = $(playerId),
			$overlay = $(playerOverlayId),
			docTitle = document.title;
		$overlay.css({
			'visibility': 'hidden',
			'opacity': 0,
		});
		$player.hide();
		document.title = docTitle.match(/\s(.*?)$/)[1];
		$player.html('');
	});
	
	// Скачать запись
	$('body').on('click', '.img_dl', function() {
		$config = $.query.get('config');
		$config = $config != false ? '&config=' + $config : '';
		$link = 'dl.php?f=' + $(this).closest('tr').data('filepath') + $config;
		window.location.href = $link;
	});	
	
	// Скачать CSV отчет
	$('body').on('click', '.dl_csv', function() {
		$config = $.query.get('config');
		$config = $config != false ? '&config=' + $config : '';
		window.location.href = 'dl.php?csv=' + $(this).data('filepath') + $config;
	});
	
	// Проверка обновлений
	$('#check-updates').on('click', function() {
		$.ajax ({
			type: 'post',
			url: '',
			data: 'check_updates=1',
			dataType: 'json',
			timeout: 7000,
			cache: false,
			success: function(data) {
				if (data['success'] === true) {
					alert(data['message']);
				} else {
					alert('Не удалось проверить обновления!');
				}
			},
			error: function(xhr, str) {
				alert('Не удалось проверить обновления!');
			},			
		});
	});
	
	// Удалить запись
	$('body').on('click', '.img_delete', function() {
		$elem = $(this);
		$str = $elem.closest('tr');
		$id = $str.data('id');
		$path = $str.data('filepath');
		$params = {
			'id' : $id,
			'path' : $path,
		};		
		if ( confirm('Вы действительно хотите удалить эту запись?') ) {
			$.ajax ({
				type: 'post',
				url: '',
				data: 'delete_record=' + JSON.stringify($params),
				dataType: 'json',
				timeout: 7000,
				cache: false,
				success: function(data) {
					if (data['success'] === true) {
						$elem.closest('.record_col').hide().html('<div class="img_notfound"></div>').fadeIn('slow');
					} else {
						alert('Ошибка удаления: ' + data['message']);
					}
				},
				error: function(xhr, str) {
					alert('Не удалось удалить запись!');
				},			
			});
		}
	});
	
	// Отправка формы
	$('#form_submit').on('click', function() {
		$form = $('form');
		$config = $.query.get('config');
		$config = $config != false ? '?config=' + $config : '';
		$.ajax ({
			type: 'post',
			url: '' + $config,
			data: $form.serialize(),
			cache: false,
			beforeSend: function(data) {
				$('#form-loader').show();
			},
			success: function(data) {
				if ( data.trim() != '' ) {
					$('#content').html(data);
				} else {
					$('#content').html('<div id="content-msg">Нет данных с выбранными параметрами</div>');
				}
				showScroll();
			},
			error: function(xhr, str) {
				$('#content').html('<div id="content-msg">Не удалось получить данные</div>');
			},
			complete: function(data) {
				$('#form-loader').hide();
			}
		});
		return false;
	});
	
	// Показать спойлеры
	$('#show_spoilers span').on('click', function() {
		$('.spoilers').toggle('fast');
		showScroll();
		return false;
	});
	
	// Изменение комментария
	$('body').on('click', '.userfield', function() {
		if (userfieldEdit === true) {
			$elem = $(this);
			$text = $elem.text().trim();
			$elem.html(
				'<div class="userfield-box">' +
					'<input data-oldtext="'+$text+'" value="'+$text+'" type="text">' +
					'<br>' +
					'<button class="btn btn-default userfield-save">&#10003;</button>' + 
					'<button class="btn btn-default userfield-cancel">&#215;</button>' +
				'</div>'
			);
			$elem.removeClass('userfield');
		}
	});
	$('body').on('click', '.userfield-save', function() {
		$elem = $(this);
		$userfield = $elem.closest('td');
		$id = $elem.closest('tr').data('id');
		$text = $userfield.find('input').val().trim();
		$params = {
			'id' : $id,
			'text' : $text,
		};
		$.ajax ({
			type: 'post',
			url: '',
			data: 'edit_userfield=' + JSON.stringify($params),
			dataType: 'json',
			timeout: 7000,
			cache: false,
			success: function(data) {
				if (data['success'] === true) {
					$userfield.find('input').data('oldtext', $text);
					$userfield.text($text).addClass('userfield');
				} else {
					$elem.removeClass('btn-default').addClass('btn-danger');
				}
			},
			error: function(xhr, str) {
				$elem.removeClass('btn-default').addClass('btn-danger');
			},
		});
	});	
	$('body').on('click', '.userfield-cancel', function() {
		$userfield = $(this).closest('td');
		$text = $userfield.find('input').data('oldtext');
		$userfield.text($text).addClass('userfield');
	});
	
	// Контекстное меню - Удаление строки из базы
	if (entryDelete === true) {
		$.contextMenu({
			selector: '.record_cdr', 
			items: {
				'delete': {
					name: 'Удалить строку',
					icon: 'delete',
					callback: function(key, options) {
						$elem = $(this);
						$str = $elem.closest('tr');
						$id = $str.data('id');
						$path = $str.data('filepath');
						$params = {
							'id' : $id,
							'path' : $path,
						};
						$.ajax ({
							type: 'post',
							url: '',
							data: 'delete_entry=' + JSON.stringify($params),
							dataType: 'json',
							timeout: 7000,
							cache: false,
							success: function(data) {
								if (data['success'] === true) {
									$str.hide();
								} else {
									alert('Не удалось удалить строку!');
								}
							},
							error: function(xhr, str) {
								alert('Не удалось удалить строку!');
							},			
						});					
					},
				},
			}
		});
	}
	
});

// Показать навигацию
function showScroll() {
	if (scrollShow === true) {
		var $bodyHeight = $('body').height(),
			$docHeight = $(window).height(),
			$scroll = $('#scroll-box');
		
		if ($bodyHeight > $docHeight) {
			$scroll.show('fast');
		} else {
			$scroll.hide('fast');
		}
	}
}

// Быстрый выбор периода
function selectRange(range) {
	switch (range) {
		// Сегодня
		case 'td':
			var date = moment().subtract(0, 'day'),
				startDay = date.format('D'),
				startMonth = date.format('MM'),
				startYear = date.format('YYYY'),
				endDay = startDay,
				endMonth = startMonth,
				endYear = startYear
			;
			break;
		// Вчера
		case 'yd':
			var date = moment().subtract(1, 'day'),
				startDay = date.format('D'),
				startMonth = date.format('MM'),
				startYear = date.format('YYYY'),
				endDay = startDay,
				endMonth = startMonth,
				endYear = startYear
			;
			break;
		// Последние 3 дня	
		case '3d':
			var dateStart = moment().subtract(2, 'day'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().subtract(0, 'day'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// Текущая неделя
		case 'tw':
			var dateStart = moment().startOf('week'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().endOf('week'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// Предыдущая неделя
		case 'pw':
			var dateStart = moment().subtract(7, 'day').startOf('week'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().subtract(7, 'day').endOf('week'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// Последние 3 недели
		case '3w':
			var dateStart = moment().subtract(2, 'week').startOf('week'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().subtract(0, 'day').endOf('week'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// Текущий месяц
		case 'tm':
			var dateStart = moment().startOf('month'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().endOf('month'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// Предыдущий месяц
		case 'pm':
			var dateStart = moment().subtract(1, 'month').startOf('month'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().subtract(1, 'month').endOf('month'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// Последние 3 месяца
		case '3m':
			var dateStart = moment().subtract(2, 'month').startOf('month'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().endOf('month'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
			break;
		// По умолчанию - Сегодня и до конца месяца
		default:
			var dateStart = moment().subtract(0, 'day'),
				startDay = dateStart.format('D'),
				startMonth = dateStart.format('MM'),
				startYear = dateStart.format('YYYY'),
				dateEnd = moment().subtract(0, 'day').endOf('month'),
				endDay = dateEnd.format('D'),
				endMonth = dateEnd.format('MM'),
				endYear = dateEnd.format('YYYY')
			;
	}

	changeStateSelect('#startday', startDay);
	changeStateSelect('#startmonth', startMonth);
	changeStateSelect('#startyear', startYear);
	changeStateSelect('#endday', endDay);
	changeStateSelect('#endmonth', endMonth);
	changeStateSelect('#endyear', endYear);
}

// Изменить состояние HTML элемента "select"
function changeStateSelect(selector, findValue) {
	var $selector = $(selector);
	$selector.find('option').each(function(index, element) {
		if ( element.value == findValue ) {
			$selector.prop('selectedIndex', index);
			return false;
		}
	});	
}

// Копирование в буфер
function initClipboard() {
	var clipboard = new Clipboard('[data-clipboard]');
	clipboard.on('success', function (e) {
		html_pulse(e.trigger, '<span class="copied">Copied!</span>');
	});
}

// Изменить текст элемента на newtext и вернуть обратно с импульсом. elem - ID элемента
function html_pulse( elem, newtext ) {
	$oldtext = $(elem).html();
	$(elem).fadeTo(
		'normal',
		0.01,
		function() {
			$(elem)
			.html(newtext)
			.css('opacity', 1)
			.fadeTo(
				'slow', 1,
				function() {
					$(elem).fadeTo('normal', 0.01, function() { $(elem).html( $oldtext ).css('opacity', 1); });
				}
			);
		}
	);
}