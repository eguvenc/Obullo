<?php

namespace Tests\Pages;

use Obullo\Router\Router;
use Obullo\View\ViewTrait;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestModel
{
    use ViewTrait;

    public function __construct(Request $request)
    {
        $this->testRequest = $request;
        $this->view = new ViewModel;
        $this->view->setTemplate('Pages/Test');
    }

    public function onGet(Request $request, Router $router)
    {
        $this->view->handler = $router->getMatchedRoute()->getHandler();

        return new HtmlResponse($this->render($this->view));
    }

    public function getRequestObject()
    {
        return $this->testRequest;
    }
}
