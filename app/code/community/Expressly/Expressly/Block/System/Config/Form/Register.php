<?php

class Expressly_Expressly_Block_System_Config_Form_Register extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('expressly/register.phtml');
        }

        return $this;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(array(
            'button_label' => Mage::helper('customer')->__($originalData['button_label']),
            'html_id' => $element->getHtmlId()
        ));

        return $this->_toHtml();
    }
}
