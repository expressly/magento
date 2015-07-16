<?php

use Expressly\Event\MerchantEvent;
use Expressly\Event\PasswordedEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;

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
        // get uuid
        $merchant = $provider->getMerchant();
        $uuid = $merchant->getUuid();
        $password = $merchant->getPassword();
        $event = new PasswordedEvent($merchant);

        try {
            if (empty($uuid) && empty($password)) {
                $event = new MerchantEvent($merchant);
                $dispatcher->dispatch('merchant.register', $event);
            } else {
                $dispatcher->dispatch('merchant.update', $event);
            }

            $content = $event->getContent();
            if (!$event->isSuccessful()) {
                throw new GenericException($content);
            }

            if (empty($uuid) && empty($password)) {
                $merchant
                    ->setUuid($content['merchantUuid'])
                    ->setPassword($content['secretKey']);

                $provider->setMerchant($merchant);
            }
        } catch (\Exception $e) {
            $app['logger']->error(ExceptionFormatter::format($e));

            $response = array('error' => -1, 'message' => $helper->__('Your values could not be transmitted to the server. Please try resubmitting, or contacting info@buyexpressly.com'));
            \Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }
}