<?php

use Expressly\Event\BannerEvent;
use Expressly\Helper\BannerHelper;
use Expressly\Subscriber\BannerSubscriber;
use Expressly\Exception\GenericException;

/**
 *
 */
class Expressly_Expressly_Block_Banner extends Mage_Core_Block_Template
{
    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $helper = new Expressly_Expressly_Helper_Client();
        $app    = $helper->getApp();

        $provider   = $app['merchant.provider'];

        $merchant = $provider->getMerchant();
        $email    = Mage::getSingleton('customer/session')->getCustomer()->getEmail();

        $event = new BannerEvent($merchant, $email);

        try {

            $helper->dispatcher->dispatch(BannerSubscriber::BANNER_REQUEST, $event);

            if (!$event->isSuccessful()) {
                throw new GenericException($this->error_formatter($event));
            }

        } catch (GenericException $e) {
            $helper->logger->error($e);
            return '';
        }

        return BannerHelper::toHtml($event);
    }

    /**
     * @param $event
     * @return string
     */
    public function error_formatter($event)
    {
        $content = $event->getContent();
        $message = array(
            $content['description']
        );
        $addBulletpoints = function ($key, $title) use ($content, &$message) {
            if (!empty($content[$key])) {
                $message[] = '<br>';
                $message[] = $title;
                $message[] = '<ul>';
                foreach ($content[$key] as $point) {
                    $message[] = "<li>{$point}</li>";
                }
                $message[] = '</ul>';
            }
        };
        // TODO: translatable
        $addBulletpoints('causes', 'Possible causes:');
        $addBulletpoints('actions', 'Possible resolutions:');
        return implode('', $message);
    }
}