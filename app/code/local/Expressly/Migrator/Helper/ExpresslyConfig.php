<?php 

/**
 * Expressly config helper.
 * @author Expressly Limited
 *
 */
class ExpresslyConfig {
	
	const OPTIONS_TABLE = "expressly_migrator_options";
	const SERVLET_URL = "https://buyexpressly.com/expresslymod";
	
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
	 * Checks if the redirect to checkout option is on or off
	 */
	public function isRedirectToCheckout(){
		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
		$modulePasswordQuery = $w->query ( "SELECT option_value FROM ".self::OPTIONS_TABLE." WHERE option_name = 'redirect_to_checkout'" );
	
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
}


?>