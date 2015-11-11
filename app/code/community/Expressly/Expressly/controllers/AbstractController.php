<?php

namespace Expressly\Expressly;

use Expressly\Event\ResponseEvent;

abstract class AbstractController extends \Mage_Core_Controller_Front_Action
{
    protected $app;
    protected $dispatcher;
    protected $logger;

    public function __construct(
        \Zend_Controller_Request_Abstract $request,
        \Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array()
    ) {
        $helper = new \Expressly_Expressly_Helper_Client();
        $this->app = $helper->getApp();
        $this->resolver = $this->app['route.resolver'];
        $this->dispatcher = $this->app['dispatcher'];
        $this->logger = $this->app['logger'];

        parent::__construct($request, $response, $invokeArgs);
    }

    public function processError(ResponseEvent $event)
    {
        $content = $event->getContent();
        $message = array(
            $content['description']
        );

        $addBulletPoints = function($data, $header) use (&$message) {
            $message[] = $header;
            foreach ($data as $point) {
                $message[] = $point;
            }
        };

        $addBulletPoints($content['causes'], 'Possible Causes:');
        $addBulletPoints($content['actions'], 'Possible Actions:');

        return implode(',', $message);
    }
}