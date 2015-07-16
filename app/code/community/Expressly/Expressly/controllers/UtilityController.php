<?php

use Expressly\Expressly\AbstractController;
use Expressly\Presenter\PingPresenter;

class Expressly_Expressly_UtilityController extends AbstractController
{
    public function pingAction()
    {
        $helper = new Expressly_Expressly_Helper_Client();

        $presenter = new PingPresenter();
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($presenter->toArray()));
    }
}
