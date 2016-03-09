<?php

use Expressly\Client;
use Expressly\Entity\MerchantType;
use Expressly\Expressly\MerchantProvider;

class Expressly_Expressly_Helper_Client extends Mage_Core_Helper_Abstract
{
    private $app;

    public function __construct()
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../controllers/AbstractController.php';

        $client = new Client(MerchantType::MAGENTO);
        $app = $client->getApp();

        $app['merchant.provider'] = function () use ($app) {
            return new MerchantProvider($app);
        };

        $this->app = $app;
    }

    public static function errorFormatter($event)
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

    public function getApp()
    {
        return $this->app;
    }

    public function getLogger()
    {
        return $this->app['logger'];
    }

    public function getDispatcher()
    {
        return $this->app['dispatcher'];
    }

    public function getMerchant()
    {
        return $this->getMerchantProvider()->getMerchant();
    }

    public function getMerchantProvider()
    {
        return $this->app['merchant.provider'];
    }
}