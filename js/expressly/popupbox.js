function popupOpen()
{
	var txt = '<div id="popup_dv00" style="width:100%;height:100%;background-color:black;border:0;margin:0;padding:0;position:fixed;top:0px;left:0px;z-index:9998;display:block;opacity:0.7;"></div>';
	txt += '<div id="popup_dv02" style="width:100%;height:100%;background-color:white;border:0;margin:0;padding:0;position:fixed;top:0px;left:0px;z-index:9999;display:block;"></div>';
	
	txt += '<div id="popup_dv01" style="width:520px;height:240px;background-color:white;border:0;margin:0;padding:0;text-align:center;position:fixed;margin-left:-289px;margin-top:-169px;left:50%;top:50%;z-index:9998;display:block;">';
	txt += '<iframe onload="offerIframeLoaded()" id="expresslyOfferFrame" src="https://buyexpressly.com/popupbox/index.php" frameborder="0" width="578" height="338" style="margin:0;border:0;padding:0;" scrolling="no"></iframe></div>';

	txt += document.getElementsByTagName('body')[0].innerHTML; document.getElementsByTagName('body')[0].innerHTML = txt;
}

function deleteAccount() {
	deleteExpresslyAccout();
}

function popupClose() 
{
	document.getElementById('popup_dv00').setAttribute('style','display:none;');
	document.getElementById('popup_dv01').setAttribute('style','display:none;');
	
	redirectToCheckout();
}

function hideWhiteOverlay() {
	document.getElementById('popup_dv02').setAttribute('style','display:none;');
}

function receiveMessage(event) 
{ 
	if (event.data == "close box") { popupClose(); }
	
	if (event.data.substr(0,4) == "url:") { window.open(event.data.substr(4),'_blank'); }
}

addEventListener("message", receiveMessage, false);