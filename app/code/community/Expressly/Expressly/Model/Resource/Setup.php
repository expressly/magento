<?php

class Expressly_Expressly_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    public static function getResourceModel()
    {
        return \Mage::getResourceModel('core/config');
    }

    public static function getBaseUrl()
    {
        return \Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, true);
    }
}