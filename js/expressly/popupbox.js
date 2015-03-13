function popupOpen() {
	document.getElementsByTagName('body')[0].innerHTML += '<div id="popup_dv02" style="width:100%;height:100%;background-color:white;border:0;margin:0;padding:0;position:fixed;top:0px;left:0px;z-index:9999;display:block;"></div>';
	
	createCall("GET", baseUrl + "expressly/index/getPopupContent?data=" + location.href.split("#")[1], function(data) {
		var txt = '<div id="popup_dv00" style="width:100%;height:100%;background-color:black;border:0;margin:0;padding:0;position:fixed;top:0px;left:0px;z-index:9998;display:block;opacity:0.7;"></div>';
		txt += '<div id="popup_dv01" style="border: 0 none;display: block;left: 0;margin-left: auto;margin-right: auto;padding: 0;position: fixed;right: 0;z-index: 9998;">';
		txt += '<div id="expresslyOfferFrame"  width="578" height="338" style="line-height:1.3;text-align:left;margin:0;border:0;padding:0;">' + data.responseText + '</div><div style="clear:both"></div></div>';
	
		txt += document.getElementsByTagName('body')[0].innerHTML;
		document.getElementsByTagName('body')[0].innerHTML = txt;
		
		hideWhiteOverlay();
	}, function () {
		alert("Failed to load offer content");
	}, "");
}

function deleteAccount() {
	deleteExpresslyAccout();
}

function popupClose() {
	document.getElementById('popup_dv00').setAttribute('style','display:none;');
	document.getElementById('popup_dv01').setAttribute('style','display:none;');
}

function popupContinue() {
	expresslyTrigger();
}

function hideWhiteOverlay() {
	document.getElementById('popup_dv02').setAttribute('style','display:none;');
}