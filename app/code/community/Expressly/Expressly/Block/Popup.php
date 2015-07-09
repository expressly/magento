<?php

/**
 *
 */
class Expressly_Expressly_Block_Popup extends Mage_Core_Block_Abstract
{
    public function _toHtml($text)
    {
        $uuid = Mage::getSingleton('core/session')->getData('xly-uuid');

        Mage::getSingleton('core/session')->unsetData('xly-uuid');

        return $uuid ? '<script type="text/javascript">alert("UUID: '.$uuid.'");</script>' : '';
    }
}