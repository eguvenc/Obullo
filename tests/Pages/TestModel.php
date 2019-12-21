<?php

namespace Tests\Pages;

use Obullo\View\ViewTrait;
use Zend\View\Model\ViewModel;
use Zend\Diactoros\Response\HtmlResponse;
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

    public function onGet(Request $request)
    {
        return new HtmlResponse($this->render($this->view));
    }

    public function getTestRequest()
    {
        return $this->testRequest;
    }
}
