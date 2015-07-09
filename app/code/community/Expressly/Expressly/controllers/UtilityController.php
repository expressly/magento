<?php

/**
 *
 */
class Expressly_Expressly_UtilityController extends Mage_Core_Controller_Front_Action
{
    public function pingAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array(
            'do' => 'utility:ping',
        )));
    }
}
