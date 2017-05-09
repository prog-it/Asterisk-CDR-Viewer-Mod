$(window).load(function() {
	if (scrollShow === true) {
		// Показать навигацию
		showScroll();
		$('#scroll-up').on('click', function() {
			$('html, body').animate({ scrollTop: 0 }, 100);
			return false;
		});
		$('#scroll-down').on('click', function() {
			$('html, body').animate({ scrollTop: $(document).height() - $(window).height() }, 100);
			return false;
		});
	}
	
	// Показать плеер
	$('.img_play').on('click', function() {
		var $player = $(playerId),
			$overlay = $(playerOverlayId),
			autoplay = (playerAutoplay === true) ? 'play' : '',
			docTitle = document.title,
			$title = (playerTitle === true) ? $(this).data('title') : '',
			content = 
				'<div class="plTitle">'+$title+'</div>' +
				'<div class="plStyle" id="player"></div>'
		;
		//link = encodeURIComponent(link);	
		$overlay.css({
			'opacity': 1,
			'visibility': 'visible',
		});
		$player.css({
			'display': 'block',
		});			
		$('title').first().html(playerSymbol + ' ' + docTitle);
		$player.html(content);
		this.aplayer = new Uppod({
			m:"audio",
			st:"uppodaudio",
			uid:"player",
			auto:autoplay,
			file:$(this).data('link'),
		});
	});
	
	// Скрыть плеер
	$('#playerOverlay').on('click', function() {
		var $player = $(playerId),
			$overlay = $(playerOverlayId),
			docTitle = document.title;
		$overlay.css({
			'visibility': 'hidden',
			'opacity': 0,
		});
		$player.css({
			'display': 'none',
		});	
		document.title = docTitle.match(/\s(.*?)$/)[1];
		$player.html('');
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
	$('.img_delete').on('click', function() {
		$elem = $(this);
		if ( confirm('Вы действительно хотите удалить эту запись?') ) {
			$.ajax ({
				type: 'post',
				url: '',
				data: 'delete_record=' + $elem.data('path'),
				dataType: 'json',
				timeout: 7000,
				cache: false,
				success: function(data) {
					if (data['success'] === true) {
						$elem.closest('.record_col').html('<div class="img_notfound"></div>');
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
	
})

// Показать навигацию
function showScroll() {
	var $bodyHeight = $('body').height(),
		$docHeight = $(window).height(),
		$scroll = $('#scroll-box');
	
	if ($bodyHeight > $docHeight) {
		$scroll.css({
			'display': 'block',
		});
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

