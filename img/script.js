
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
	var curr = new Date,
		first,
		last;
	
	switch (range) {
		case 'td':
			first = curr.getDate();
			last = new Date(curr.setDate(first));
			first = new Date(curr.setDate(first));
			break;
		case 'yd':
			first = curr.getDate()-1;
			last = new Date(curr.setDate(first));
			first = new Date(curr.setDate(first));
			break;
		case '3d':
			first = curr.getDate()-2;
			last = new Date(curr.setDate(first+2));
			first = new Date(curr.setDate(first));
			break;
		case 'tw':
			// В Воскресенье не работает. Выводится дата на след. неделю
			first = curr.getDate()-curr.getDay()+1;
			last = first + 6;
			first = new Date((new Date(curr)).setDate(first));
			last = new Date((new Date(curr)).setDate(last));
			break;
		case 'pw':
			first = curr.getDate()-7-curr.getDay()+1;
			last = new Date(curr.setDate(first+6));
			first = new Date(curr.setDate(first));
			break;
		case '3w':
			// В Воскресенье не работает. Выводится дата на след. неделю
			first = curr.getDate()-curr.getDay()+1;
			last = first + 6;
			last = new Date((new Date(curr)).setDate(last));
			first = curr.getDate()-14-curr.getDay()+1;
			first = new Date((new Date(curr)).setDate(first));
			break;
		case 'tm':
			first = new Date(curr.getFullYear(), curr.getMonth(), 1);
			last = new Date(curr.getFullYear(), curr.getMonth()+1, 0);
			break;
		case 'pm':
			first = new Date(curr.getFullYear(), curr.getMonth()-1, 1);
			last = new Date(curr.getFullYear(), curr.getMonth(), 0);
			break;	
		case '3m':
			first = new Date(curr.getFullYear(), curr.getMonth()-2, 1);
			last = new Date(curr.getFullYear(), curr.getMonth()+1, 0);
			break;
		default:
			first = curr.getDate();
			last = new Date(curr.getFullYear(), curr.getMonth()+1, 0);
			first = new Date(curr.setDate(first));
	}
	
	if (typeof(first) !== 'undefined') {
		$('#startmonth').prop('selectedIndex', first.getMonth());
		$('#startday').val(first.getDate());
		
		var $selector = $('#startyear');
		$selector.find('option').each(function(index, element) {
			if ( element.value == first.getFullYear() ) {
				$selector.prop('selectedIndex', index);
				return false;
			}
		});
		$('#endmonth').prop('selectedIndex', last.getMonth());
		$('#endday').val(last.getDate());
		
		$selector = $('#endyear');
		$selector.find('option').each(function(index, element) {
			if ( element.value == last.getFullYear() ) {
				$selector.prop('selectedIndex', index);
				return false;
			}
		});		
	}
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