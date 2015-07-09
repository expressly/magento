<?php

/**
 *
 */
class Expressly_Expressly_BatchController extends Mage_Core_Controller_Front_Action
{
    public function invoiceAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array(
            'do' => 'batch:invoice',
        )));
    }

    public function customerAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array(
            'do' => 'batch:customer',
        )));
    }
}
