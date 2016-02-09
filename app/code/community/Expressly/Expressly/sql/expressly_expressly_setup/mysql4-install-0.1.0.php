<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Expressly\Expressly\MerchantProvider;

$installer = $this;
$installer->startSetup();

$resource = $installer->getResourceModel();
$resource->loadToXml(\Mage::getConfig());
$storeId = \Mage::app()->getStore()->getId();
$host = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
$base = \Mage::getBaseUrl(\Mage_Core_Model_Store::URL_TYPE_WEB);
$path = trim(str_replace($host, '', $base));

$resource->saveConfig('expressly/expressly_general/expressly_destination', '/', 'default', 0);
$resource->saveConfig(MerchantProvider::PATH, ($path == '/') ? '' : $path, 'default', 0);
$resource->saveConfig(MerchantProvider::HOST, $host, 'default', 0);
$resource->saveConfig('expressly/expressly_general/expressly_image', $base . \Mage::getStoreConfig('design/header/logo_src'), 'default', 0);
$resource->saveConfig('expressly/expressly_general/expressly_offer', true, 'default', 0);
$resource->saveConfig('expressly/expressly_general/expressly_password', '', 'default', 0);
$resource->saveConfig('expressly/expressly_general/expressly_policy', $base, 'default', 0);
$resource->saveConfig('expressly/expressly_general/expressly_terms', $base, 'default', 0);
$resource->saveConfig('expressly/expressly_general/expressly_uuid', '', 'default', 0);

$installer->endSetup();