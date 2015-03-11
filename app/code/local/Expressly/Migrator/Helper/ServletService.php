<?php
require_once 'app/code/local/Expressly/Migrator/Helper/ExpresslyConfig.php';

/**
 * Service layer for Servlet related operations
 * @author Expressly Limited
 *
 */
class ServletService {
	
	/**
	 * Sends a migration request to the servlet
	 * @param unknown $referrer is the current URL of the module
	 */
	public function sendMigrationRequest($data) {
		$contentFromServlet = array();
		$contentFromServlet['content'] = "";
		$headersToSend = "";
	
		$paramters = array (
				'data' => $data
		);
		
		$options = array (
				'http' => array (
						'header' => "Referer: " . Mage::getUrl() . "\r\n",
						'method' => 'GET',
						'ignore_errors' => true,
						'timeout' => 120
				)
		);
		
		$context = stream_context_create($options);
		$contentFromServlet['content'] = file_get_contents(ExpresslyConfig::SERVLET_URL."/newmigration?".http_build_query($paramters), false, $context);
		
		$contentFromServlet['headers'] = array();

		foreach($http_response_header as $header) {
			array_push($contentFromServlet['headers'], $header);
		}
		
		return $contentFromServlet;
	}
	
	/**
	 * Sends the initial password to the servlet
	 * @param unknown $password
	 */
	public function sendInitialPassword($merchantUrl, $password) {
		$data = array (
				'newPass' => $password,
	            'webshopSystem' => 'Magento'
		);
		
		$options = array (
				'http' => array (
						'header' => "Content-Type: application/x-www-form-urlencoded\r\nReferer: " . $merchantUrl . "\r\n",
						'method' => 'POST',
						'content' => http_build_query ( $data )
				)
		);
		
		$context = stream_context_create ( $options );
		file_get_contents (ExpresslyConfig::SERVLET_URL."/saveModulePassword", false, $context );
	}
	
	
	/**
	 * Send the module password to the servlet
	 */
	public function sendNewModulePassword($oldPass, $newPass) {
		$data = array (
				'oldPass' => $oldPass,
				'newPass' => $newPass 
		);
		
		$options = array (
				'http' => array (
						'header' => "Content-Type: application/x-www-form-urlencoded\r\nReferer: " . Mage::getStoreConfig('web/secure/base_url') . "\r\n",
						'method' => 'POST',
						'content' => http_build_query ( $data ) 
				) 
		);
		
		$context = stream_context_create ( $options );
		return "ok" == file_get_contents (ExpresslyConfig::SERVLET_URL."/updateModulePassword", false, $context );
	}
}