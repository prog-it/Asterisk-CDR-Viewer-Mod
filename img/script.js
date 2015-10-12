
// ID элемента с плеером
var playerId = 'playerBox';
// Автовоспроизведение
var playerAutoplay = true;
// Показ даты записи
var playerTitle = true;
// Символ, который будет добавлен в Title во время воспроизведения
var playerSymbol = '&#9835;&#9835;&#9835;';

// Показать запись
function showRecord(link, title) {
	var elem = document.getElementById(playerId);
	var autoplay = (playerAutoplay === true) ? '&amp;auto=play' : '';
	var docTitle = document.title;
	title = (playerTitle === true) ? 'Дата: '+title : '';
	link = encodeURIComponent(link);
	var content = 
			'<div class="objPlayer">' +
				'<div class="objTitle">'+title+'</div>' +
				'<object class="obj" type="application/x-shockwave-flash" data="img/player.swf" width="425" height="40">' +
					'<param movie="img/player.swf">' +
					'<param name="FlashVars" value="file='+link+'&amp;m=audio&amp;st=img/player_style.txt'+autoplay+'">' +
				'</object>' +
			'</div>'
			;
	
	elem.style.opacity = 1;
	elem.style.visibility = 'visible';
	document.getElementsByTagName('title')[0].innerHTML = playerSymbol + ' ' + docTitle;
	elem.innerHTML = content;
}

// Скрыть запись
function hideRecord() {
	var elem = document.getElementById(playerId);
	var docTitle = document.title;
	elem.style.visibility = 'hidden';
	elem.style.opacity = 0;
	document.title = docTitle.match(/\s(.*?)$/)[1];
	elem.innerHTML = '';
}

// Быстрый выбор периода
function selectRange(range) {
	var curr = new Date;
	var first;
	var last;
	
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
			first = curr.getDate()-curr.getDay()+1;
			last = new Date(curr.setDate(first+6));
			first = new Date(curr.setDate(first));
			break;
		case 'pw':
			first = curr.getDate()-7-curr.getDay()+1;
			last = new Date(curr.setDate(first+6));
			first = new Date(curr.setDate(first));
			break;
		case '3w':
			first = curr.getDate()-14-curr.getDay()+1;
			last = new Date(curr.setDate(first+20));
			first = new Date(curr.setDate(first));
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
		document.getElementById('startmonth').selectedIndex = first.getMonth();
		document.getElementById('startday').value = first.getDate();
		
		var selector = document.getElementById('startyear');
		for (i = selector.options.length-1; i>=0; i--) {
			if (selector.options[i].value == first.getFullYear()) {
				selector.selectedIndex = i;
				break;
			}
		}
		document.getElementById('endmonth').selectedIndex = last.getMonth();
		document.getElementById('endday').value = last.getDate();
		
		selector = document.getElementById('endyear');
		for (i = selector.options.length-1; i>=0; i--) {
			if (selector.options[i].value == last.getFullYear()) {
				selector.selectedIndex = i;
				break;
			}
		}
	}
	
}

// Показать навигацию
function showScroll() {
	var bodyHeight = document.body.clientHeight;
	var docHeight = document.documentElement.clientHeight;
	var scroll = document.getElementById('scrollBox');
	
	if (bodyHeight > docHeight) {
		scroll.style.display = 'block';
	}
}




