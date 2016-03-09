<?php

namespace Expressly\Expressly;

use Expressly\Entity\Merchant;
use Expressly\Provider\MerchantProviderInterface;
use Pimple\Container;

class MerchantProvider implements MerchantProviderInterface
{
    private $app;
    private $merchant;

    const APIKEY = 'expressly/expressly_general/expressly_apikey';
    const PATH = 'expressly/expressly_general/expressly_path';
    const HOST = 'expressly/expressly_general/expressly_host';

    public function __construct(Container $app)
    {
        $this->app = $app;

        $this->updateMerchant();
    }

    private function updateMerchant()
    {
        $merchant = new Merchant();
        $merchant
            ->setApiKey($this->getParameter(self::APIKEY))
            ->setPath($this->getParameter(self::PATH))
            ->setHost($this->getParameter(self::HOST));

        $this->merchant = $merchant;
    }

    private function getParameter($key)
    {
        return \Mage::getStoreConfig($key, \Mage::app()->getStore());
    }

    public function setMerchant(Merchant $merchant)
    {
        $model = \Mage::getModel('core/config');

        $model->saveConfig(self::APIKEY, $merchant->getApiKey(), 'default', 0);
        $model->saveConfig(self::PATH, $merchant->getPath(), 'default', 0);
        $model->saveConfig(self::HOST, $merchant->getHost(), 'default', 0);

        $this->merchant = $merchant;
    }

    public function getMerchant($update = false)
    {
        if ($update) {
            $this->updateMerchant();
        }

        return $this->merchant;
    }
}