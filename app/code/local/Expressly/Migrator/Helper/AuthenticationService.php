<?php

/**
 * Authentication service
 * @author Expressly Limited
 *
 */
class AuthenticastionService {
	
	const OPTIONS_TABLE = "expressly_migrator_options";
	
	/**
	 * Checks if the request is authorized or not.
	 */
	public function isAuthorizedRequest($request) {
		$auth = $request->getHeader ( "Authorization" );
		$authParts = explode(" ", $auth);
		
		return $authParts[0] == "Expressly" && $authParts[1] == base64_encode($this->getAuthToken());
	}
	
	/**
	 * Reads the authorization token from the db.
	 */
	public function getAuthToken() {
		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$modulePasswordQuery = $w->query ( "SELECT option_value FROM " . self::OPTIONS_TABLE . " WHERE option_name = 'module_password'" );
		
		$row = $modulePasswordQuery->fetch ();
		
		return $row ['option_value'];
	}
}