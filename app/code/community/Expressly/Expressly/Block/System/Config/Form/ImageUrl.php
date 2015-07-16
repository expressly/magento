<?php

/**
 *
 */
class Expressly_Expressly_Block_System_Config_Form_ImageUrl extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $extra = '';

        if ($element->getValue()) {
            $extra = '<img style="max-height:100px;" src="' . $element->getValue() . '" /><br />';
        }

        return $extra . parent::_getElementHtml($element);
    }
}
