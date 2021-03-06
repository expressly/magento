<?php

use Expressly\Entity\Address;
use Expressly\Entity\Customer;
use Expressly\Entity\Email;
use Expressly\Entity\Phone;
use Expressly\Entity\Route;
use Expressly\Event\CustomerMigrateEvent;
use Expressly\Event\MerchantEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Expressly\AbstractController;
use Expressly\Presenter\CustomerMigratePresenter;
use Expressly\Subscriber\CustomerMigrationSubscriber;

class Expressly_Expressly_CustomerController extends AbstractController
{
    public function showAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $route = $this->resolver->process(preg_replace('/.*(expressly\/.*)/i', '/${1}', $_SERVER['REQUEST_URI']));

        if ($route instanceof Route) {
            $emailAddress = $this->getRequest()->getParam('email');
            $merchant = $this->app['merchant.provider']->getMerchant();

            try {
                $mageCustomer = \Mage::getModel('customer/customer');
                $mageCustomer->setWebsiteId(\Mage::app()->getWebsite()->getId());
                $mageCustomer->loadByEmail($emailAddress);

                $reference = $mageCustomer->getId();
                if ($reference) {
                    $customer = new Customer();
                    $customer
                        ->setFirstName($mageCustomer->getFirstname())
                        ->setLastName($mageCustomer->getLastname())
                        ->setDateUpdated(new DateTime($mageCustomer->getCreatedAt()));

                    if ($mageCustomer->getDob()) {
                        $customer->setBirthday(new DateTime($mageCustomer->getDob()));
                    }

                    if ($mageCustomer->getTaxvat()) {
                        $customer->setTaxNumber($mageCustomer->getTaxvat());
                    }

                    $gender = $mageCustomer->getGender();
                    if ($gender == 1 || $gender == 2) {
                        $customer->setGender($mageCustomer->getGender() == 1 ? 'M' : 'F');
                    }

                    $email = new Email();
                    $email
                        ->setAlias('default')
                        ->setEmail($emailAddress);
                    $customer->addEmail($email);

                    $defaultBilling = $mageCustomer->getDefaultBilling();
                    $defaultShipping = $mageCustomer->getDefaultShipping();
                    foreach ($mageCustomer->getAddresses() as $mageAddress) {
                        $address = new Address();
                        $address
                            ->setFirstName($mageAddress->getFirstname())
                            ->setLastName($mageAddress->getLastname())
                            ->setCompanyName($mageAddress->getCompany())
                            ->setAddress1($mageAddress->getStreet1())
                            ->setAddress2($mageAddress->getStreet2())
                            ->setCity($mageAddress->getCity())
                            ->setStateProvince($mageAddress->getRegionCode())
                            ->setZip($mageAddress->getPostcode())
                            ->setCountry($mageAddress->getCountry());

                        $phone = new Phone();
                        $phone
                            ->setType(Phone::PHONE_TYPE_HOME)
                            ->setNumber($mageAddress->getTelephone());
                        $customer->addPhone($phone);
                        $address->setPhonePosition($customer->getPhoneIndex($phone));

                        $primary = false;
                        $type = null;
                        if ($mageAddress->getId() == $defaultBilling) {
                            $primary = true;
                            $type = Address::ADDRESS_BILLING;
                        }
                        if ($mageAddress->getId() == $defaultShipping) {
                            $primary = true;
                            $type = ($type == Address::ADDRESS_BILLING) ? Address::ADDRESS_BOTH : Address::ADDRESS_SHIPPING;
                        }

                        $customer->addAddress($address, $primary, $type);
                    };

                    $presenter = new CustomerMigratePresenter($merchant, $customer, $emailAddress, $reference);
                    $this->getResponse()->setBody(json_encode($presenter->toArray()));
                } else {
                    $this->getResponse()->setHttpResponseCode(404);
                }
            } catch (\Exception $e) {
                $this->logger->error(ExceptionFormatter::format($e));
                $this->getResponse()->setBody(json_encode(array()));
            }
        } else {
            $this->getResponse()->setHttpResponseCode(401);
        }
    }

    public function migrateAction()
    {
        $uuid = $this->getRequest()->getParam('uuid');
        $exists = false;
        $xlyerror = '';

        try {
            $merchant = $this->app['merchant.provider']->getMerchant();
            $event = new CustomerMigrateEvent($merchant, $uuid);
            $this->dispatcher->dispatch('customer.migrate.data', $event);

            $json = $event->getContent();
            if (!$event->isSuccessful()) {
                if (!empty($json['code']) && $json['code'] == 'USER_ALREADY_MIGRATED') {
                    $exists = true;
                }

                throw new GenericException($this->processError($event));
            }

            $mageCustomer = \Mage::getModel('customer/customer');
            $mageCustomer->setWebsiteId(\Mage::app()->getWebsite()->getId());

            $email = $json['migration']['data']['email'];
            $mageCustomer->loadByEmail($email);

            if ($mageCustomer->getId()) {
                $exists = true;
                $event = new CustomerMigrateEvent($merchant, $uuid, CustomerMigrateEvent::EXISTING_CUSTOMER);
            } else {
                $customer = $json['migration']['data']['customerData'];

                $mageCustomer
                    ->setStore(\Mage::app()->getStore())
                    ->setFirstname($customer['firstName'])
                    ->setLastname($customer['lastName'])
                    ->setEmail($email)
                    ->setPassword(md5('xly' . microtime()))
                    ->setIsSubscribed(!$customer['optoutNewsletter']);

                if ($customer['dob']) {
                    $mageCustomer->setDob($customer['dob']);
                }

                if ($customer['taxNumber']) {
                    $mageCustomer->setTaxvat($customer['taxNumber']);
                }

                if ($customer['gender'] && ($customer['gender'] == 'F' || $customer['gender'] == 'M')) {
                    $mageCustomer->setGender($customer['gender'] == 'M' ? 1 : 2);
                }

                $mageCustomer->save();

                $countryProvider = $this->app['country_code.provider'];
                foreach ($customer['addresses'] as $index => $address) {
                    $mageAddress = \Mage::getModel('customer/address');

                    $safelyGet = function ($key) use ($address) {
                        if (!empty($address[$key])) {
                            return $address[$key];
                        }

                        return '';
                    };

                    $mageAddress
                        ->setCustomerId($mageCustomer->getId())
                        ->setFirstname($address['firstName'])
                        ->setLastname($address['lastName'])
                        ->setCountryId($countryProvider->getIso2($address['country']))
                        ->setPostcode($safelyGet('zip'))
                        ->setCity($safelyGet('city'))
                        ->setRegion($safelyGet('stateProvince'))
                        ->setTelephone($customer['phones'][$address['phone']]['number'])
                        ->setCompany($safelyGet('company'))
                        ->setStreet(sprintf("%s\n%s", $safelyGet('address1'), $safelyGet('address2')))
                        ->setSaveInAddressBook(true);

                    if ($customer['billingAddress'] == $index) {
                        $mageAddress->setIsDefaultBilling(true);
                    }

                    if ($customer['shippingAddress'] == $index) {
                        $mageAddress->setIsDefaultShipping(true);
                    }

                    $mageAddress->save();
                }

                // Send out password forgotten email
                $token = \Mage::helper('customer')->generateResetPasswordLinkToken();
                $mageCustomer->changeResetPasswordLinkToken($token);
                $mageCustomer->sendPasswordReminderEmail();
                $mageCustomer->save();

                // log user in
                \Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($mageCustomer);
            }

            if (!empty($json['cart']['productId'])) {
                $cart = \Mage::getModel('checkout/cart');
                $cart->truncate();
                $cart->addProduct($json['cart']['productId']);
                $cart->save();
            }

            if (!empty($json['cart']['couponCode'])) {
                \Mage::getSingleton('checkout/cart')
                    ->getQuote()
                    ->getShippingAddress()
                    ->setCollectShippingRates(true);

                \Mage::getSingleton('checkout/cart')
                    ->getQuote()
                    ->setCouponCode($json['cart']['couponCode'])
                    ->collectTotals()
                    ->save();
            }
            \Mage::getSingleton('checkout/session')->setCartWasUpdated(
                !empty($json['cart']['couponCode']) ||
                !empty($json['cart']['productId']));

            $this->getResponse()->setRedirect('https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/success');
            return;
        } catch (\Exception $e) {
            $this->logger->error(ExceptionFormatter::format($e));
            $xlyerror = $e->getMessage();
        }

        if (!$exists) {
            $this->getResponse()->setRedirect('https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/failed?e=' . urlencode($xlyerror));
        } else {
            $this->getResponse()->setRedirect('https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/exists?loginUrl=' . urlencode(Mage::getBaseUrl() . 'customer/account/login') );
        }
    }

    public function popupAction()
    {
        $uuid = $this->getRequest()->getParam('uuid');
        $merchant = $this->merchantProvider->getMerchant();
        $event = new CustomerMigrateEvent($merchant, $uuid);

        try {
            $this->dispatcher->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_POPUP, $event);

            if (!$event->isSuccessful()) {
                throw new GenericException($this->processError($event));
            }

            $this->mimicFrontPage();
            $this->getResponse()->appendBody($event->getContent());
        } catch (\Exception $e) {
            $this->logger->error(ExceptionFormatter::format($e));
            $this->getResponse()->setRedirect('https://prod.expresslyapp.com/api/redirect/migration/' . $uuid . '/failed?e=' . urlencode($e->getMessage()));
        }
    }

    private function mimicFrontPage()
    {
        $page = \Mage::getModel('cms/page');
        $page->setStoreId(\Mage::app()->getStore()->getId());
        $page->load(\Mage::getStoreConfig('web/default/cms_home_page'), 'identifier');
        \Mage::helper('cms/page')->renderPage($this, $page->getId());
    }
}
