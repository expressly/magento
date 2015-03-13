<?php
require_once 'app/code/local/Expressly/Migrator/Helper/ServletService.php';

/**
 * Initial setup for expressly migrator
 */

$configModel = new Mage_Core_Model_Config();
$servletService = new ServletService();

$password = md5(uniqid(rand(), true));

$w = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
$modulePasswordQuery = $w->query("SELECT value FROM core_config_data WHERE path = 'web/secure/base_url'");
$row = $modulePasswordQuery->fetch();

$servletService->sendInitialPassword($row['value'], $password);

$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `expressly_migrator_options` (
      `id` int(11) NOT NULL auto_increment,
      `option_name` text,
      `option_value` text,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	INSERT INTO `expressly_migrator_options` VALUES (1,'module_password','".$password."');
	INSERT INTO `expressly_migrator_options` VALUES (2,'post_checkout_box','false');
	INSERT INTO `expressly_migrator_options` VALUES (3,'redirect_enabled','true');
    INSERT INTO `expressly_migrator_options` VALUES (4,'redirect_to_login','true');
	INSERT INTO `expressly_migrator_options` VALUES (5,'redirect_destination','checkout/cart');
");
$installer->endSetup();
?>