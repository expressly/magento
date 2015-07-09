<?php

/**
 *
 */
class Expressly_Expressly_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function showAction()
    {
        $email = $this->getRequest()->getParam('email');

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array(
            'do'    => 'customer:show',
            'email' => $email,
        )));
    }

    public function migrateAction()
    {
        $uuid = $this->getRequest()->getParam('uuid');

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(array(
            'do'   => 'customer:migrate',
            'uuid' => $uuid,
        )));
    }

    public function popupAction()
    {
        $uuid = $this->getRequest()->getParam('uuid');

        Mage::getSingleton('core/session')->setData('xly-uuid', $uuid);

        $this->getResponse()->setRedirect(Mage::getBaseUrl());
    }
}
