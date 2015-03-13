<?php
require_once 'app/code/local/Expressly/Migrator/Helper/ExpresslyConfig.php';
require_once 'app/code/local/Expressly/Migrator/Helper/AuthenticationService.php';

/**
 * Magento MVC controller for expressly admin module
 *
 * @author Expressly Limited
 *        
 */
class Expressly_Migrator_AdminController extends Mage_Core_Controller_Front_Action {
	
    const OPTIONS_TABLE = "expressly_migrator_options";
    private $authService;
    
    /**
     * Constructor
     */
    protected function _construct() {
    	$this->authService = new AuthenticastionService();
    }
    
    /**
     * Updates the redirect to login option
     */
    public function updateRedirectToLoginAction(){
        $newValue = $this->getRequest()->getParam('redirect-to-login');
        if($newValue != null && $newValue != "") {
            $w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
            $data = array("option_value" => $newValue);
            $where = "option_name = 'redirect_to_login'";
            $w->update(self::OPTIONS_TABLE, $data, $where);
        }
    }
    
    /**
     * Updates the redirect user option
     */
    public function updateRedirectEnabledAction(){
    	$newValue = $this->getRequest()->getParam('redirect-enabled');
    	if($newValue != null && $newValue != "") {
    		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
    		$data = array("option_value" => $newValue);
    		$where = "option_name = 'redirect_enabled'";
    		$w->update(self::OPTIONS_TABLE, $data, $where);
    	}
    }
    
    /**
     * Updates the post checkout box option
     */
    public function updatePostCheckoutAction(){
    	$newValue = $this->getRequest()->getParam('post-checkout-box');
    	if($newValue != null && $newValue != "") {
    		$w = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_write' );
    		$data = array("option_value" => $newValue);
    		$where = "option_name = 'post_checkout_box'";
    		$w->update(self::OPTIONS_TABLE, $data, $where);
    	}
    }
	
	/**
	 * Delete user endpoint [site]/[module]/admin/deleteUserByMail
	 * 
	 * Used by the servlet, to delete the test users
	 */
	public function deleteUserByMailAction() {
        if($this->authService->isAuthorizedRequest($this->getRequest())) {
			Mage::register('isSecureArea', true);
			
			$customer = Mage::getModel("customer/customer");
			$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
			$customer = $customer->loadByEmail($this->getRequest()->getParam('user_mail'));
			$customer->delete();
			
			Mage::unregister('isSecureArea');
		} else {
		    $this->getResponse()->setHttpResponseCode(401);
		}
	}
}
?>