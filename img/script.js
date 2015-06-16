
// ID элемента с плеером
var playerId = 'playerBox';

// Показать запись
function showRecord(link) {
	var elem = document.getElementById(playerId);
	link = encodeURIComponent(link);
	var content = 
			'<div class="objPlayer">' +
				'<object class="obj" type="application/x-shockwave-flash" data="img/player.swf" width="425" height="40">' +
					'<param movie="img/player.swf">' +
					'<param name="FlashVars" value="file='+link+'&amp;m=audio&amp;st=img/player_style.txt">' +
				'</object>' +
			'</div>'
			;
	
	elem.style.opacity='1';
	elem.style.visibility='visible';
	elem.innerHTML=content;
}

// Скрыть запись
function hideRecord() {
	var elem = document.getElementById(playerId);
	elem.style.visibility='hidden';
	elem.style.opacity='0';
	elem.innerHTML='';
}


