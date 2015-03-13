<?php 

/**
 * Expressly config helper.
 * @author Expressly Limited
 *
 */
class ExpresslyConfig {
	
	const OPTIONS_TABLE = "expressly_migrator_options";
	const SERVLET_URL = "https://buyexpressly.com/expresslymod";
	const POPUP_URL = "http://buyexpressly.com/website/popup_demo_a_four/index.php";
	const POPUP_MOBILE_URL = "http://buyexpressly.com/website/popup_demo_a_four/index.php";
	
	/**
	 * Idicates if we want to show the post checkout box or not.
	 */
	public function isPostCheckOutBox(){
		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$modulePasswordQuery = $w->query ( "SELECT option_value FROM ".self::OPTIONS_TABLE." WHERE option_name = 'post_checkout_box'" );
		
		$row = $modulePasswordQuery->fetch();
			
		return $row['option_value'];
	}
	
	/**
	 * Checks if the redirect user option is on or off
	 */
	public function isRedirectEnabled(){
		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$modulePasswordQuery = $w->query ( "SELECT option_value FROM ".self::OPTIONS_TABLE." WHERE option_name = 'redirect_enabled'" );
	
		$row = $modulePasswordQuery->fetch();
			
		return $row['option_value'];
	}
	
	/**
	 * Checks if the redirect to login option is on or off
	 */
	public function isRedirectToLogin(){
	    $w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
	    $modulePasswordQuery = $w->query ( "SELECT option_value FROM ".self::OPTIONS_TABLE." WHERE option_name = 'redirect_to_login'" );
	
	    $row = $modulePasswordQuery->fetch();
	    	
	    return $row['option_value'];
	}
	
	/**
	 * Gets the redirect destination value
	 */
	public function getRedirectDestination() {
		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$modulePasswordQuery = $w->query ( "SELECT option_value FROM " . self::OPTIONS_TABLE . " WHERE option_name = 'redirect_destination'" );
	
		$row = $modulePasswordQuery->fetch ();
	
		return $row ['option_value'];
	}
}


?>