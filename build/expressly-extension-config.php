<?php
return array(
    //The base_dir and archive_file path are combined to point to your tar archive
    //The basic idea is a separate process builds the tar file, then this finds it
    'base_dir'               => dirname(__FILE__).'/../out/staging',
    'archive_files'          => 'Expressly-Staging.tar',

    //The Magento Connect extension name.  Must be unique on Magento Connect
    //Has no relation to your code module name.  Will be the Connect extension name
    'extension_name'         => 'Expressly',

    //Your extension version.  By default, if you're creating an extension from a
    //single Magento module, the tar-to-connect script will look to make sure this
    //matches the module version.  You can skip this check by setting the
    //skip_version_compare value to true
    'extension_version'      => 'auto',
    'skip_version_compare'   => false,

    //You can also have the package script use the version in the module you
    //are packaging with.
    'auto_detect_version'   => true,

    //Where on your local system you'd like to build the files to
    'path_output'            => dirname(__FILE__).'/../out',

    //Magento Connect license value.
    'stability'              => 'stable',

    //Magento Connect license value
    'license'                => 'MIT',

    //Magento Connect channel value.  This should almost always (always?) be community
    'channel'                => 'community',

    //Magento Connect information fields.
    'summary'                => 'Expressly is a customer referral network that allows merchants to find partner merchants and launch customer acquisition campaigns to their respective audiences. Full instructions, and registration at www.buyexpressly.com',
    'description'            => 'Expressly is a customer referral network where merchants connect with each other to acquire customers and drive sales. On the Expressly Network, you can find the perfect match with a non-competing (usually complementary) merchant. If the connection is then accepted, you will be able to acquire new customers and drive your sales up. Thanks to our unique customer journey, customers won\'t have to register to your site again, removing unnecessary friction. We are able to migrate their data, saving them time and delivering to you a real customer with one click. This will amazingly increase your conversions rates at a surprisingly cheap cost. Full instructions and registration at www.buyexpressly.com',
    'notes'                  => 'https://github.com/expressly/magento/releases',

    //Magento Connect author information. If author_email is foo@example.com, script will
    //prompt you for the correct name.  Should match your http://www.magentocommerce.com/
    //login email address
    'author_name'            => 'Expressly',
    'author_user'            => 'Expressly',
    'author_email'           => 'info@expressly.com',

    //PHP min/max fields for Connect.  I don't know if anyone uses these, but you should
    //probably check that they're accurate
    'php_min'                => '5.3.0',
    'php_max'                => '6.0.0'
);