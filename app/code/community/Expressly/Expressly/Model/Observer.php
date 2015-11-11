<?php

use Expressly\Event\MerchantEvent;
use Expressly\Event\PasswordedEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Exception\InvalidAPIKeyException;
use Expressly\Subscriber\MerchantSubscriber;

class Expressly_Expressly_Model_Observer extends Varien_Event_Observer
{
    public function controllerFrontInitBefore($observer)
    {
        require_once __DIR__ . '/../controllers/AbstractController.php';
    }

    public function registerUpdateMerchant($observer)
    {
        $helper = new Expressly_Expressly_Helper_Client();
        $app = $helper->getApp();
        $provider = $app['merchant.provider'];
        $dispatcher = $app['dispatcher'];

        $merchant = $provider->getMerchant();
        $event = new PasswordedEvent($merchant);

        try {
            $provider->setMerchant($merchant);
            $dispatcher->dispatch(MerchantSubscriber::MERCHANT_REGISTER, $event);

            if (!$event->isSuccessful()) {
                throw new InvalidAPIKeyException();
            }
        } catch (\Exception $e) {
            $app['logger']->error(ExceptionFormatter::format($e));

            $response = array(
                'error' => -1,
                'message' => $helper->__('Your values could not be transmitted to the server. Please try resubmitting, or contacting info@buyexpressly.com')
            );
            \Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }
}