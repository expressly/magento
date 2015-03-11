var newCustomerName = "";
var newDiscount = "";

/**
 * Creates a CORS request
 */
function createCORSRequest(method, url) {
	var xhr = new XMLHttpRequest();
	
	if (typeof XDomainRequest != "undefined") {
		xhr = new XDomainRequest();
		xhr.open(method, url);
	} else if ("withCredentials" in xhr) {
		xhr.open(method, url, true);
	} else {
		xhr = null;
	}
	return xhr;
}

/**
 * Creates a call
 */
function createCall(method, url, callbackOnSuccess, callbackOnFail, authHeader, postMarameters) {
	var xhr = createCORSRequest(method, url);

	if (!xhr) {
		throw new Error('CORS not supported');
	}

	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhr.timeout = 120000;
    xhr.ontimeout = function () { 
    	alert("AJAX timeout"); 
    }
	
	if(authHeader) {
		xhr.setRequestHeader('Authorization', authHeader);
	}
	
	xhr.onload = function() {
		callbackOnSuccess(xhr);
	};

	xhr.onerror = function() {
		callbackOnFail(xhr);
	};

	if(postMarameters) {
		xhr.send(postMarameters);
	} else {
		xhr.send();
	}
}

/**
 * Starts the migration process.
 */
function expresslyTrigger() {
	
	var hashParameters = location.href.split("#")[1];

	if (hashParameters) {
		
		createCall("GET", baseUrl + "expressly/index/migration?data=" + hashParameters, function(xhr) {
			var responseText = xhr.responseText;
			if (xhr.readyState == 4 && xhr.status == 200) {
				
				// Ping the checkout page
				createCall("GET", baseUrl + "checkout/onepage", function() {
		        });
				var responseArray = xhr.responseText.split(";");
				
				// Used by the offer frame logic to update the content.
				document.body.innerHTML += '<input type="hidden" id="expresslyCr" name="expresslyCr" value="' + responseArray[0] + '"/>';
				
				newCustomerName = responseArray[0];
				newDiscount = responseArray[1];

				hideWhiteOverlay();
			} else if(xhr.readyState == 4 && xhr.status == 204) {
				hideWhiteOverlay();
				alert("Migration error - User does not exist in A");
			} else if(xhr.readyState == 4 && xhr.status == 409) {
				if(isRedirectToLoginEnabled) {
					var loginDataArray = xhr.responseText.split("|");
					setCookie("expresslylogindata", loginDataArray[0]);
					
					createCall("GET", baseUrl + "/expressly/index/addProductAndCoupon?user_email="+loginDataArray[0]+"&product_id="+loginDataArray[1]+"&coupon_code="+loginDataArray[2], function() {
						// Ping the checkout page
						createCall("GET", baseUrl + "checkout/onepage", function() {
							hideWhiteOverlay();
							alert("You already have an account here.");
							window.location.replace(baseUrl + "customer/account/login");
				        });
			        });
				} else {
					hideWhiteOverlay();
					alert("You already have an account here.");
				}
			} else {
				hideWhiteOverlay();
				alert("Migration fail");
			}
		}, function(xhr) {
			hideWhiteOverlay();
			alert("Migration fail");
		});
	}
}

/**
 * Event handler for ifram loaded
 */
function offerIframeLoaded() {
	if(newCustomerName != "" && newDiscount != "") {
		document.getElementById("expresslyOfferFrame").contentWindow.postMessage('updateUserData:' + newCustomerName + ';' + newDiscount + '%', '*');
	}
}

/**
 * Generates hash for the offer.
 */
function generateHashForOffer() {
	createCall("GET", servletUrl + "/admin/getmodulehash?email=" + document.querySelector('#expresslyUserEmail').value, function(xhr) {
		var responseText = xhr.responseText;
		if (xhr.readyState == 4 && xhr.status == 200) {
			document.querySelector('#expresslyOfferLink').href = xhr.responseText;
		}
	}, function(xhr) {
		alert("There was an error.");
	});
}

/**
 * Redirects to the checkout page
 */
function redirectToCheckout() {
	if(isRedirectToCheckoutEnabled) {
		window.location.replace(baseUrl + "checkout/onepage");
	}
}

/**
 * Document load event
 */
document.observe('dom:loaded', function(){ 
	var image = document.querySelector('#expresslyOfferImage');
	var landingPageBody = document.querySelector('.cms-index-index');
	var loginEmailField = document.querySelector('input#email');
	
	if(image) {
		generateHashForOffer();
	}
	
	if(landingPageBody && window.location.hash != "") {
		popupOpen();
	}
	
	if(loginEmailField && isRedirectToLoginEnabled) {
		var loginData = getCookie("expresslylogindata");
		loginEmailField.value = loginData;
		deleteCookie("expresslylogindata");
	}
});

/**
 * Sets a cookie
 * @param name is the cookie name
 * @param value is the cookie value
 */
function setCookie(name, value) {
	var d = new Date();
	d.setTime(d.getTime() + (10 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = name + "=" + value + "; " + expires;
}

/**
 * Deletes a cookie
 */
function deleteCookie (cookieName) {
	document.cookie = cookieName + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC"; 
}

/**
 * Gets a cookie by name.
 * @param name is the cookie name
 * @returns the cookie value
 */
function getCookie(name) {
	var value = "";
	var name = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) != -1) {
			value = c.substring(name.length, c.length);
		}
	}
	return value;
}