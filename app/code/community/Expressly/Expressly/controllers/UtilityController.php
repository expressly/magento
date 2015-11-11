<?php

use Expressly\Entity\Route;
use Expressly\Expressly\AbstractController;
use Expressly\Presenter\PingPresenter;
use Expressly\Presenter\RegisteredPresenter;

class Expressly_Expressly_UtilityController extends AbstractController
{
    public function pingAction()
    {
        $presenter = new PingPresenter();
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($presenter->toArray()));
    }

    public function registeredAction()
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $route = $this->resolver->process($_SERVER['REQUEST_URI']);

        if ($route instanceof Route) {
            $presenter = new RegisteredPresenter();
            $this->getResponse()->setBody(json_encode($presenter->toArray()));
        } else {
            $this->getResponse()->setHttpResponseCode(401);
        }
    }
}
