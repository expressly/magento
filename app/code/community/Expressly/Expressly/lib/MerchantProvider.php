<?php

namespace Expressly\Expressly;

use Expressly\Entity\Merchant;
use Expressly\Provider\MerchantProviderInterface;
use Silex\Application;

class MerchantProvider implements MerchantProviderInterface
{
    private $app;
    private $merchant;

    const DESTINATION = 'expressly/expressly_general/expressly_destination';
    const PATH = 'expressly/expressly_general/expressly_path';
    const HOST = 'expressly/expressly_general/expressly_host';
    const IMAGE = 'expressly/expressly_general/expressly_image';
    const NAME = 'design/header/logo_alt';
    const OFFER = 'expressly/expressly_general/expressly_offer';
    const PASSWORD = 'expressly/expressly_general/expressly_password';
    const POLICY = 'expressly/expressly_general/expressly_policy';
    const TERMS = 'expressly/expressly_general/expressly_terms';
    const UUID = 'expressly/expressly_general/expressly_uuid';

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->updateMerchant();
    }

    private function updateMerchant()
    {
        $merchant = new Merchant();
        $merchant
            ->setDestination($this->getParameter(self::DESTINATION))
            ->setPath($this->getParameter(self::PATH))
            ->setHost($this->getParameter(self::HOST))
            ->setImage($this->getParameter(self::IMAGE))
            ->setName($this->getParameter(self::NAME))
            ->setOffer($this->getParameter(self::OFFER))
            ->setPassword($this->getParameter(self::PASSWORD))
            ->setPolicy($this->getParameter(self::POLICY))
            ->setTerms($this->getParameter(self::TERMS))
            ->setUuid($this->getParameter(self::UUID));

        $this->merchant = $merchant;
    }

    private function getParameter($key)
    {
        return \Mage::getStoreConfig($key, \Mage::app()->getStore());
    }

    public function setMerchant(Merchant $merchant)
    {
        $model = \Mage::getModel('core/config');

        $model->saveConfig(self::DESTINATION, $merchant->getDestination(), 'default', 0);
        $model->saveConfig(self::PATH, $merchant->getPath(), 'default', 0);
        $model->saveConfig(self::HOST, $merchant->getHost(), 'default', 0);
        $model->saveConfig(self::IMAGE, $merchant->getImage(), 'default', 0);
        $model->saveConfig(self::OFFER, $merchant->getOffer(), 'default', 0);
        $model->saveConfig(self::PASSWORD, $merchant->getPassword(), 'default', 0);
        $model->saveConfig(self::POLICY, $merchant->getPolicy(), 'default', 0);
        $model->saveConfig(self::TERMS, $merchant->getTerms(), 'default', 0);
        $model->saveConfig(self::UUID, $merchant->getUuid(), 'default', 0);

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