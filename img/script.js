
// ID элемента с плеером
var playerId = 'playerBox';
// Показывать ли дату записи
var playerTitle = true;
// Автопроигрывание
var playerAutoplay = true;

// Показать запись
function showRecord(link, title) {
	var elem = document.getElementById(playerId);
	var autoplay = (playerAutoplay == true) ? 'autoplay="autoplay"' : '';
	title = (playerTitle === true) ? 'Дата: '+title : '';
	
	        var content =
                        '<div class="objPlayer">' +
				'<div class="objTitle">'+title+'</div>' +
                                '<td><audio '+autoplay+'src="'+link+'" type=\"audio/wav\" controls=\"controls\"></td>\n'+
                        '</div>'
                        ;

	elem.style.opacity = 1;
	elem.style.visibility = 'visible';
	elem.innerHTML = content;
}

// Скрыть запись
function hideRecord() {
	var elem = document.getElementById(playerId);
	elem.style.visibility = 'hidden';
	elem.style.opacity = 0;
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




