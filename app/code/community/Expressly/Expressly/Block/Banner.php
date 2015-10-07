<?php

use Expressly\Event\BannerEvent;
use Expressly\Exception\GenericException;
use Expressly\Helper\BannerHelper;
use Expressly\Subscriber\BannerSubscriber;

class Expressly_Expressly_Block_Banner extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $helper = new Expressly_Expressly_Helper_Client();
        $app = $helper->getApp();

        $provider = $app['merchant.provider'];

        $merchant = $provider->getMerchant();
        $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();

        $event = new BannerEvent($merchant, $email);

        try {
            $helper->dispatcher->dispatch(BannerSubscriber::BANNER_REQUEST, $event);

            if (!$event->isSuccessful()) {
                throw new GenericException(Expressly_Expressly_Helper_Client::errorFormatter($event));
            }

        } catch (GenericException $e) {
            $helper->logger->error($e);

            return '';
        }

        return BannerHelper::toHtml($event);
    }
}