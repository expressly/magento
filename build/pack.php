<?php

require_once 'magento-1.9.2.4/Archive.php';
require_once 'magento-1.9.2.4/Archive/Interface.php';
require_once 'magento-1.9.2.4/Archive/Abstract.php';
require_once 'magento-1.9.2.4/Archive/Gz.php';
require_once 'magento-1.9.2.4/Archive/Bz.php';
require_once 'magento-1.9.2.4/Archive/Tar.php';
require_once 'magento-1.9.2.4/Archive/Helper/File.php';
require_once 'magento-1.9.2.4/Archive/Helper/File/Gz.php';
require_once 'magento-1.9.2.4/Archive/Helper/File/Bz.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (count($argv) === 3) {
    list(, $dir, $file) = $argv;
    $archive = new Mage_Archive();
    $archive->pack($dir, $file, true);
} else {
    throw new Exception('Invalid number of arguments. Usage: php pack.php [directory to archive] [archive name]');
}

