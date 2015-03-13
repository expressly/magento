/**
 * Expressly module administration related JavaScript logic
 */
var loadingMessage = "&nbsp;....................................................................................";
var postData = 'parameter={"customer":{"entity_id":"2","entity_type_id":"1","attribute_set_id":"0","website_id":"1","email":"endpoint.test@buyexpressly.com","group_id":"1","increment_id":null,"store_id":"0","created_at":"2014-10-07 14:57:06","updated_at":"2014-10-07 15:12:47","is_active":"1","disable_auto_group_change":"0","firstname":"zs","lastname":"zs","password_hash":"87ebc0c0071d2e44737a1589638e6a05:uL","created_in":"Admin","default_billing":"1","default_shipping":"1"},"addresses":[{"entity_id":"1","entity_type_id":"2","attribute_set_id":"0","increment_id":null,"parent_id":"2","created_at":"2014-10-07 15:07:35","updated_at":"2014-10-07 15:12:47","is_active":"1","firstname":"zs","lastname":"zs","city":"sdf","region":"American Samoa","postcode":"34df","country_id":"US","telephone":"3434343","region_id":"3","street":"sdss","customer_id":"2"}]}';

/**
 * Shows the content of the desired how to fix div
 */
function showHowToFixContent(element) {
	document.querySelector('#' + element + " .howtofix_content").style.display = "inline";
}

/**
 * Runs the endpoint tests.
 */
function runEndpointTests() {
	checkSelfStoreUserEndpount();
	checkServletEndpoints();
}

/**
 * Checks the store user endpoint
 */
function checkSelfStoreUserEndpount() {
	document.querySelector('#checkStep1').innerHTML += loadingMessage;
	// Needs to embedd the calls inside eachother to create a sequence
	// Check store user endpoint
	createCall("POST", baseUrl + "/expressly/index/storeUser", function(data) {
		
		var newUserId = data.responseText.split("|")[0];
		
		if(data.readyState == 4 && data.status == 200 && !isNaN(newUserId)) {
			createCall("GET", baseUrl + "/expressly/admin/deleteUserByMail?user_mail=endpoint.test@buyexpressly.com", function(data) {
				document.querySelector('.modulechecstep_1_result').innerHTML = tickIco;
				checkSelfUserInformationEndpoint();
			}, function () {}, "Expressly " + authToken);
		} else {
			document.querySelector('.modulechecstep_1_result').innerHTML = crossIco;
			document.querySelector('#modulechecstep_1_howtofix').style.display = "inline";
		}
	}, function () {}, "Expressly " + authToken, postData);
}

/**
 * Checks the get user information endpoint.
 */
function checkSelfUserInformationEndpoint() {
	var getUserInfoResults = new Array();
	var anyFailedUserInfoTests = false;
	
	document.querySelector('#checkStep2').innerHTML += loadingMessage;
		
	// Check the get user info endpoint
	// Case 1: Check endpoint without token
	createCall("GET", baseUrl + "/expressly/index/getUser?user_id=", function(data) {
		// Endpoint should not be accessible without a token
		getUserInfoResults.push(data.readyState == 4 && data.status == 401);
			
		// Case 2: Check endpoint with token
		createCall("GET", baseUrl + "/expressly/index/getUser?user_id=", function(data) {
			// Endpoint should not be accessible without a token
			getUserInfoResults.push(data.readyState == 4 && data.status == 200 && data.responseText == '{"customer":[],"addresses":[]}');
			
			// Case 3: check an actual get user info
			createCall("POST", baseUrl + "/expressly/index/storeUser", function(data) {
				
				var newUserId = data.responseText.split("|")[0];
				
				if(data.readyState == 4 && data.status == 200 && !isNaN(newUserId)) {
					createCall("GET", baseUrl + "/expressly/index/getUser?user_mail=endpoint.test@buyexpressly.com", function(data) {
						
						getUserInfoResults.push(data.responseText.indexOf('"entity_id":"' + newUserId + '"') > -1);
						
						createCall("GET", baseUrl + "/expressly/admin/deleteUserByMail?user_mail=endpoint.test@buyexpressly.com", function(data) {
							document.querySelector('.modulechecstep_1_result').innerHTML = tickIco;
						}, function () {}, "Expressly " + authToken);
						
						// Check the result
						for(var i = 0; i < getUserInfoResults.length; i++) {
							if(getUserInfoResults[i] == false) {
								anyFailedUserInfoTests = true;
								break;
							}
						}
						
						if(!anyFailedUserInfoTests) {
							document.querySelector('.modulechecstep_2_result').innerHTML = tickIco;
						} else {
							document.querySelector('.modulechecstep_2_result').innerHTML = crossIco;
							document.querySelector('#modulechecstep_2_howtofix').style.display = "inline";
						}
					}, function () {}, "Expressly " + authToken, postData);
				} else {
					document.querySelector('.modulechecstep_2_result').innerHTML = crossIco;
					document.querySelector('#modulechecstep_2_howtofix').style.display = "inline";
				}
			}, function () {}, "Expressly " + authToken, postData);
		}, function () {}, "Expressly " + authToken);
	});
}

/**
 * Checks the external endpoint
 */
function checkServletEndpoints() {
	document.querySelector('#checkStep5').innerHTML += loadingMessage;
	
	createCall("GET", baseUrl + "expressly/index/migration?data=false", function (data) {
		if(data.readyState == 4 && data.status == 500) {
			document.querySelector('.modulechecstep_5_result').innerHTML = tickIco;
		} else {
			document.querySelector('.modulechecstep_5_result').innerHTML = crossIco;
			document.querySelector('#modulechecstep_5_howtofix').style.display = "inline";
		}
	});
}

/**
 * Updates the postcheckout content appearance
 */
function updatePostCheckoutBox(selfElement) {
	createCall("GET", baseUrl + "/expressly/admin/updatePostCheckout?post-checkout-box=" + selfElement.checked, function (data) {
	}, function () {});
}

/**
 * Updates the redirect user option
 */
function updateRedirectEnabled(selfElement) {
	var textField = document.querySelector('#redirect-destination-field');
	textField.disabled = !textField.disabled;
	
	var testLink = document.querySelector('#userRedirectionTestLink');
	if(testLink.style.display == "none") {
		testLink.style.display = "inline";
	} else {
		testLink.style.display = "none";
	}
	
	createCall("GET", baseUrl + "/expressly/admin/updateRedirectEnabled?redirect-enabled=" + selfElement.checked, function (data) {
	}, function () {});
}

/**
 * Updates the redirect to login option
 */
function updateRedirectToLogin(selfElement) {
	createCall("GET", baseUrl + "/expressly/admin/updateRedirectToLogin?redirect-to-login=" + selfElement.checked, function (data) {
	}, function () {});
}

/**
 * Tests the user redirection
 */
function testUserRedirection() {
	if(checkNewRedirectAddress()) {
		var destinationValue = document.querySelector('#redirect-destination-field').value;
		
		if(destinationValue.indexOf("http") > -1) {
			window.open(destinationValue);
		} else {
			window.open(baseUrl + destinationValue);
		}
	}
}

/**
 * Checks if the new redirect destination value is valid.
 * @returns {Boolean}
 */
function checkNewRedirectAddress() {
	var returnValue = true;
	var destinationValue = document.querySelector('#redirect-destination-field').value;
	var webshopRoot = baseUrl.replace("index.php/", "");
	
	if((destinationValue.indexOf("http") > -1 && destinationValue.indexOf(webshopRoot) == -1) || destinationValue.indexOf("/") == 0) {
		alert("Please check the format of the redirect url you're pasting. It should be relative to " + webshopRoot + ".");
		returnValue = false;
	}
	
	return returnValue;
}