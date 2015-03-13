<?php
require_once 'app/code/local/Expressly/Migrator/Helper/ExpresslyConfig.php';
require_once 'app/code/local/Expressly/Migrator/Helper/AuthenticationService.php';
require_once 'app/code/local/Expressly/Migrator/Helper/ServletService.php';

/**
 * Controller for admin tab
 * @author Expressly Limited
 *
 */
class Expressly_Migrator_TabController extends Mage_Adminhtml_Controller_Action {

    const OPTIONS_TABLE = "expressly_migrator_options";
    private $authService;
    private $servletService;
    
    /**
     * Constructor
     */
    protected function _construct() {
        $this->authService = new AuthenticastionService();
        $this->servletService = new ServletService();
    }
    
    /**
     * Index action method
     */
    public function indexAction() {
        $config = new ExpresslyConfig();
        $this->loadLayout();
        	
        Mage::register('postCheckoutBox', $config->isPostCheckOutBox());
        Mage::register('redirectEnabled', $config->isRedirectEnabled());
        Mage::register('redirectToLogin', $config->isRedirectToLogin());
        Mage::register('redirectDestination', $config->getRedirectDestination());
        Mage::register('modulePass', base64_encode($this->authService->getAuthToken()));
        Mage::register('pureModulePass', $this->authService->getAuthToken());
        
        $this->_setActiveMenu('expressly_menu');
        $this->renderLayout();
    }
    
    /**
     * Updates the redirect destination.
     */
    public function storeRedirectDestinationAction() {
    	$newValue = $this->getRequest ()->getParam ('redirect-destination');
    	$baseUrl = str_replace("index.php/", "", Mage::getUrl());
    	
    	if(strpos($newValue, $baseUrl) !== false) {
    		$newValue = str_replace($baseUrl, "", $newValue);
    	}
    	
    	$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
    	$data = array("option_value" => $newValue);
    	$where = "option_name = 'redirect_destination'";
    	$w->update(self::OPTIONS_TABLE, $data, $where);
    	$message = $this->__('User redirect destination has been updated successfully.');
    	Mage::getSingleton('adminhtml/session')->addSuccess($message);
    	
    	$this->_redirect('*/*/index');
    }
    
    /**
     * Handles the module password update
     */
    public function storeModulePassAction() {
        $newPass = $this->getRequest ()->getParam ('modulePass');
        
        if($newPass != null && $newPass != "") {
            if(!$this->servletService->sendNewModulePassword($this->authService->getAuthToken(), $newPass)) {
                $message = $this->__('Failed to send new password to expressly.');
                Mage::getSingleton('adminhtml/session')->addError($message);
            } else {
                $w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
                $data = array("option_value" => $newPass);
                $where = "option_name = 'module_password'";
                $w->update(self::OPTIONS_TABLE, $data, $where);
                $message = $this->__('Module password has been updated successfully.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            }
        }
        
        $this->_redirect('*/*/index');
    }
}
?>