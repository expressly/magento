<?php

use Expressly\Event\BannerEvent;
use Expressly\Exception\GenericException;
use Expressly\Helper\BannerHelper;
use Expressly\Subscriber\BannerSubscriber;

class Expressly_Expressly_Block_Banner extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        try {
            $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();

            if (!$email) {
                return '';
            }

            $helper = new Expressly_Expressly_Helper_Client();
            $merchant = $helper->getMerchant();
            $event = new BannerEvent($merchant, $email);

            try {
                $helper->getDispatcher()->dispatch(BannerSubscriber::BANNER_REQUEST, $event);

                if (!$event->isSuccessful()) {
                    throw new GenericException(Expressly_Expressly_Helper_Client::errorFormatter($event));
                }

            } catch (GenericException $e) {
                $helper->getLogger()->error($e);

                return '';
            }

            return BannerHelper::toHtml($event);
        } catch (Exception $ignore) {
            // never break the shop
            return '';
        }
    }
}