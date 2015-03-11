<?php
require_once 'app/code/local/Expressly/Migrator/Helper/AuthenticationService.php';
require_once 'app/code/local/Expressly/Migrator/Helper/ServletService.php';

/**
 * Magento MVC controller for expressly
 * @author Expressly Limited
 *
 */
class Expressly_Migrator_IndexController extends Mage_Core_Controller_Front_Action
{
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
	 * Endpoint for the migration logic.
	 */
	public function migrationAction() {
		$response = $this->getResponse();
		$servletContent = $this->servletService->sendMigrationRequest($this->getRequest()->getParam('data'));
		$status = "";
		
		foreach($servletContent['headers'] as $header) {
		    $headerparts = explode(":", $header);

		    if(count($headerparts) == 1) {
		        $statusParts = explode(" ", $header);
		        $status = $statusParts[1];
		        break;
		    }
		}
		
		$response->setHttpResponseCode(intval($status));
		
		if(intval($status) == 409) {
		    $responseBody = $servletContent['content'];
		} else {
		    $responseArray = explode("|", $servletContent['content']);
		    
		    $customer = $this->loginUser($responseArray[0], $responseArray[1], $responseArray[2]);
		    
		    $coupon = Mage::getModel('salesrule/coupon')->load($responseArray[2], 'code');
		    $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
		    
		    $responseBody = $customer->getFirstname().";".floor($rule['discount_amount']);
		}
		
 		$response->setBody($responseBody);
	}
	
    /**
     * Get user information endpoint [site]/[module]/index/getUser
     */
    public function getUserAction() {
        if($this->authService->isAuthorizedRequest($this->getRequest())) {
        	$this->getResponse()->setHeader('Content-type', 'application/json');
        	
        	$customer = Mage::getModel("customer/customer"); 
        	$customer->setWebsiteId(Mage::app()->getWebsite()->getId()); 
        	$customer = $customer->loadByEmail($this->getRequest()->getParam('user_mail'));
    
        	$addresses = array();
        	$responseObject = array();
        	
        	foreach ($customer->getAddresses() as $address)
        	{
        		$addresses[] = $address->toArray();
        	}

        	$responseObject['customer'] = $customer->toArray();
        	$responseObject['addresses'] = $addresses;
        	
        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseObject));
        } else {
            $this->getResponse()->setHttpResponseCode(401);
        }
    }
    
    /**
     * Store user endpoint [site]/[module]/index/storeUser
     */
    public function storeUserAction() {
        if($this->authService->isAuthorizedRequest($this->getRequest())) {
        	$parameter = $this->getRequest()->getParam('parameter');
    		
        	if($parameter != null) {
    
        		$requestObject = Mage::helper('core')->jsonDecode($parameter);
        		$customerInRequest = $requestObject['customer'];
        		$addressesInRequest = $requestObject['addresses'];
    
    	    	try{
    		    	$customer = Mage::getModel("customer/customer");
    		    	$customer->setWebsiteId($customerInRequest['website_id']);
    			    $customer->setStoreId($customerInRequest['store_id']);
    			    $customer->setFirstname($customerInRequest['firstname']);
    			    $customer->setLastname($customerInRequest['lastname']);
    			    $customer->setEmail($customerInRequest['email']);
    			    $customer->setPasswordHash($customerInRequest['password_hash']);
    	    		
    			    $customer->save();
    			    
    	    	} catch (Exception $e) {
    	    		echo "Error during saving customer: " . $e->getMessage();
    	    	}
    	    	
    	    	try{
    	    	    
    	    		foreach($addressesInRequest as $address) {
    
    	    			$firstName = $address['firstname'];
    	    			$middleName = $address['middlename'];
    	    			$lastName = $address['lastname'];
    	    			$countryId = $address['country_id'];
    	    			$regionId = $address['region_id'];
    	    			$region = $address['region'];
    	    			$postCode = $address['postcode'];
    	    			$telephone = $address['telephone'];
    	    			$fax = $address['fax'];
    	    			$company = $address['company'];
    	    			$city = $address['city'];
    	    			$street = $address['street'];
    	    			$defaultBilling = $customerInRequest['default_billing'] == $address['entity_id'] ? '1' : '0';
    	    			$defaultShiopping = $customerInRequest['default_shipping'] == $address['entity_id'] ? '1' : '0';
    	    			
            	    	$address = Mage::getModel("customer/address");
            	    	$address->setCustomerId($customer->getId())
            		    	->setFirstname($firstName)
            		    	->setMiddleName($middleName)
            		    	->setLastname($lastName)
            		    	->setCountryId($countryId)
            		    	->setRegionId($regionId)
            		    	->setRegion($region)
            		    	->setPostcode($postCode)
            		    	->setCity($city)
            		    	->setTelephone($telephone)
            		    	->setFax($fax)
            		    	->setCompany($company)
            		    	->setStreet($street)
            		    	->setIsDefaultBilling($defaultBilling)
            		    	->setIsDefaultShipping($defaultShiopping)
            		    	->setSaveInAddressBook('1');
            	    	
            	    		$address->save();
    	    		}
    	    	} catch (Exception $e) {
    	    		echo "Error during saving address: " . $e->getMessage();
    	    	}
    	    	
    			$this->getResponse()->setBody($customer->getId());
        	} else {
        	    $this->getResponse()->setHttpResponseCode(401);
        	}
    	}
    }
	
	/**
	 * Adds a product and a coupon to the cart.
	 */
    public function addProductAndCouponAction() {
        $productId = $this->getRequest()->getParam('product_id');
        $couponCode = $this->getRequest()->getParam('coupon_code');
        $userEmail = $this->getRequest()->getParam('user_email');
        
        $this->addProductAndCoupon($productId, $couponCode, $userEmail);
    }
    
    /**
     * Checks if the given user has any orders.
     */
    public function checkUserHasAnyOrderAction() {
        if($this->authService->isAuthorizedRequest($this->getRequest())) {
            $userEmail = $this->getRequest()->getParam('user_email');
            
            echo $this->userHasOrders($userEmail) ? 1 : 0;
        }
    }

    /**
     * Logs in the user, and redirects to the checkout page.
     */
    private function loginUser($userId, $productId, $couponCode) {
        $customer = Mage::getModel('customer/customer')->load($userId);
        
        try {
            $session = Mage::getSingleton('customer/session');
            $session->setCustomerAsLoggedIn($customer);
        } catch (Exception $e) {
            echo "Failed to log in user" . $e->getMessage();
        }
        
        $this->addProductAndCoupon($productId, $couponCode, $customer->getEmail());
        
        return $customer;
    }
    
    /**
     * Adds a product and a coupon to the cart.
     * @param $productId is the product id
     * @param $couponCode is the coupon code.
     * @param $userEmail is the email address of the user
     */
    private function addProductAndCoupon($productId, $couponCode, $userEmail) {
        $productAdded = false;
        $couponAdded = false;
        
        if(!$this->userHasOrders($userEmail)) {
            if ($productId != null && $productId != "") {
                try {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $cart = Mage::getModel('checkout/cart');
                    $cart->truncate();
                    $cart->init();
                    $cart->addProduct($product, array (
                            'qty' => 1 
                    ));
                    $cart->save();
                    
                    $productAdded = true;
                } catch (Exception $e) {
                    echo "Failed to add product" . $e->getMessage();
                }
            }
            
            if ($couponCode != null && $couponCode != "") {
                try {
                    Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
                    
                    $couponAdded = true;
                } catch (Exception $e) {
                    echo "Failed to add coupon" . $e->getMessage();
                }
            }
            
            if ($productAdded || $couponAdded) {
                Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                
                Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            }
        }
    }

    /**
     * Checks if the user has any orders or not.
     */
    private function userHasOrders($userEmail) {
        
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer = $customer->loadByEmail($userEmail);
        
        $collection = Mage::getResourceModel("sales/order_collection")->addFieldToSelect('*')->addFieldToFilter('customer_id', $customer->getId());
        
        return count($collection) > 0;
    }
}
?>