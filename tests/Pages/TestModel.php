<?php

namespace Tests\Pages;

use Obullo\View\ModelTrait;
use Zend\View\Model\ViewModel;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestModel
{
    use ModelTrait;

    public function __construct(Request $request)
    {
        $this->testRequest = $request;
        $this->viewModel = new ViewModel;
        $this->viewModel->setTemplate('Pages/Test');
    }

    public function onGet(Request $request)
    {
        return new HtmlResponse($this->render($this->viewModel));
    }

    public function getTestRequest()
    {
        return $this->testRequest;
    }
}
