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
	 * Gets all the e-mail addresses from the system.
	 */
	public function getAllEmailAddressesAction() {
		if($this->authService->isAuthorizedRequest($this->getRequest())) {
			$response = "";	
			$collection = Mage::getModel('customer/customer')
	                  ->getCollection()
	                  ->addAttributeToSelect('*');
			
			foreach($collection as $user) {
				if($response != "") {
					$response .= "|";
				}
				
				$response .= $user->getEmail();
			}
			$this->getResponse()->setBody($response);
		} else {
            $this->getResponse()->setHttpResponseCode(401);
        }
	}
	
	/**
	 * Gets a customer name by it's e-mail address
	 */
	public function getUserNameAction() {
		if($this->authService->isAuthorizedRequest($this->getRequest())) {
        	$customer = Mage::getModel("customer/customer"); 
        	$customer->setWebsiteId(Mage::app()->getWebsite()->getId()); 
        	$customer = $customer->loadByEmail($this->getRequest()->getParam('user_email'));
        	
        	$this->getResponse()->setBody($customer->getFirstname());
        } else {
            $this->getResponse()->setHttpResponseCode(401);
        }
	}
	
	/**
	 * Loads the popup content
	 */
	public function getPopupContentAction() {
		$popupUrl = $this->isMobile() ? ExpresslyConfig::POPUP_MOBILE_URL : ExpresslyConfig::POPUP_URL;
		
		$paramtersToServlet = array (
				'data' => $this->getRequest()->getParam('data')
		);
		
		$paramtersToPopup = array ();
		
		$options = array (
				'http' => array (
						'header' => "Content-type: application/x-www-form-urlencoded\r\n",
						'method' => 'GET',
						'ignore_errors' => true
				)
		);
		
		$context = stream_context_create($options);
		
		$userNameAndCouponCode = file_get_contents(ExpresslyConfig::SERVLET_URL."/getUserName?".http_build_query($paramtersToServlet), false, $context);
		$popupContent = file_get_contents($popupUrl.http_build_query($paramtersToPopup), false, $context);
		
		if($userNameAndCouponCode != "" && strpos($userNameAndCouponCode, "|") !== false) {
			$responseArray = explode("|", $userNameAndCouponCode);
			$coupon = Mage::getModel('salesrule/coupon')->load($responseArray[1], 'code');
			$rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
			$amount = $rule['simple_action'] == Mage_SalesRule_Model_Rule::BY_FIXED_ACTION
				? Mage::helper('core')->currency(floor($rule['discount_amount']), true, false) : floor($rule['discount_amount'])."%";
			
			$popupContent = str_replace("Customer", $responseArray[0], $popupContent);
			$popupContent = str_replace("discount", $amount, $popupContent);
		}
		
	    $this->getResponse()->setBody($popupContent);
	}
	
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
		
		if (intval($status) == 200) {
		    $responseArray = explode("|", $servletContent['content']);
		    
		    $customer = $this->loginUser($responseArray[0], $responseArray[1], $responseArray[2]);
		    
		    $this->sendPasswordResetMail($customer);
		    
		    if($customer != null && $this->getRequest()->getParam('subscribeNewsLetter') == "true") {
				Mage::getModel('newsletter/subscriber')->subscribe($customer->getEmail());
		    }
		} else {
			$responseBody = $servletContent['content'];
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
        	if($customer->getPasswordHash() != null) {
        		$customer->setPasswordHash("0:0");
        	}
        	
        	$addresses = array();
        	$responseObject = array();
        	
        	foreach ($customer->getAddresses() as $address)	{
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
    	    		$defaultWebshiteId = Mage::app()->getWebsite()->getId();
    	    		$defaultStoreId = Mage::app()->getWebsite()->getDefaultGroup()->getDefaultStoreId();
    	    		
    		    	$customer = Mage::getModel("customer/customer");
    		    	$customer->setWebsiteId($customerInRequest['website_id'] == "" ? $defaultWebshiteId : $customerInRequest['website_id']);
    			    $customer->setStoreId($customerInRequest['store_id'] == "" ? $defaultStoreId : $customerInRequest['store_id']);
    			    $customer->setFirstname($customerInRequest['firstname']);
    			    $customer->setLastname($customerInRequest['lastname']);
    			    $customer->setEmail($customerInRequest['email']);
    			    $customer->setPasswordHash("0:0");
    	    		
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
            $orders = $this->getOrders($userEmail);
            $totalValue = 0;
            
            if(count($orders) > 0) {
            	$firstOrder = null;
            	foreach($orders as $order) {
            		$firstOrder = $order;
            		break;
            	}
            	
            	$totalValue = $firstOrder->getGrandTotal();
            }
            
            echo $totalValue;
        }
    }

    /**
     * Logs in the user, and adds the product and coupon
     */
    private function loginUser($userId, $productId, $couponCode) {
        $customer = Mage::getModel('customer/customer')->load($userId);
        
        try {
            $session = Mage::getSingleton('customer/session');
            // $session->loginById($customer->getId());
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
                    $cart = Mage::getSingleton('checkout/cart');
                    $cart->truncate();
                    $cart->init();
                    $cart->addProduct($product, array (
                            'qty' => 1 
                    ));
                    $cart->save();
                    $cart->setCartWasUpdated(true);
                    $cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    $cart->getQuote()->setTotalsCollectedFlag(false);
                    
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
        }
    }

    /**
     * Checks if the user has any orders or not.
     */
    private function userHasOrders($userEmail) {
        return count($this->getOrders($userEmail)) > 0;
    }
    
    /**
     * Gets the orders of a user
     * @param unknown $userEmail
     */
    private function getOrders($userEmail) {
    	$customer = Mage::getModel("customer/customer");
    	$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
    	$customer = $customer->loadByEmail($userEmail);
    	
    	return Mage::getResourceModel("sales/order_collection")->addFieldToSelect('*')->addFieldToFilter('customer_id', $customer->getId());
    }
    
    /**
     * Sends the password reset e-mail
     */
    private function sendPasswordResetMail($customer) {
    	try {
    		$customer->changeResetPasswordLinkToken(Mage::helper('customer')->generateResetPasswordLinkToken());
    		
    		$mailer = Mage::getModel('core/email_template_mailer');
    		$emailInfo = Mage::getModel('core/email_info');
    		$emailInfo->addTo($customer->getEmail(), $customer->getFirstname());
    		$mailer->addEmailInfo($emailInfo);
    		
    		// Set all required params and send emails
    		$mailer->setSender(Mage::getStoreConfig('customer/password/forgot_email_identity',  Mage::app()->getStore()->getStoreId()));
    		$mailer->setStoreId(Mage::app()->getStore()->getStoreId());
    		$mailer->setTemplateId('expressly_password_reset_template');
    		$mailer->setTemplateParams(array('customer' => $customer, 'store' => Mage::app()->getStore()));
    		$mailer->send();
    	} catch(Exception $e) {
    		echo "Failed to send password reset e-mail: " . $e->getMessage();
    	}
    }
    
    /**
     * Checks if the user agent is mobile or not.
     * @return boolean
     */
    private function isMobile() {
    	$useragent=$_SERVER['HTTP_USER_AGENT'];
    	return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
    }
}
?>