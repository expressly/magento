<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$installer = $this;
$installer->startSetup();

$resource = $installer->getResourceModel();
$resource->loadToXml(\Mage::getConfig());
$storeId = \Mage::app()->getStore()->getId();
$host = 'https://' . $_SERVER['HTTP_HOST'];
$base = \Mage::getBaseUrl(\Mage_Core_Model_Store::URL_TYPE_WEB);

$resource->saveConfig(Expressly\Expressly\MerchantProvider::APIKEY, '', 'default', 0);
$resource->saveConfig(Expressly\Expressly\MerchantProvider::PATH, '/', 'default', 0);
$resource->saveConfig(Expressly\Expressly\MerchantProvider::HOST, $host, 'default', 0);

$resource->deleteConfig('expressly/expressly_general/expressly_image', 'default', 0);
$resource->deleteConfig('expressly/expressly_general/expressly_terms', 'default', 0);
$resource->deleteConfig('expressly/expressly_general/expressly_policy', 'default', 0);
$resource->deleteConfig('expressly/expressly_general/expressly_password', 'default', 0);
$resource->deleteConfig('expressly/expressly_general/expressly_destination', 'default', 0);
$resource->deleteConfig('expressly/expressly_general/expressly_offer', 'default', 0);
$resource->deleteConfig('expressly/expressly_general/expressly_uuid', 'default', 0);

$installer->endSetup();