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

        $app['merchant.provider'] = $app->share(function () use ($app) {
            return new MerchantProvider($app);
        });

        $this->app = $app;
        $this->dispatcher = $app['dispatcher'];
        $this->logger = $app['logger'];
    }

    public function getApp()
    {
        return $this->app;
    }
}