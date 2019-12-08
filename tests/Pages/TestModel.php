<?php

namespace Tests\Pages;

use Obullo\PageTrait;
use Zend\View\Model\ViewModel;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestModel
{
    use PageTrait;

    public function __construct()
    {
        $this->viewModel = new ViewModel;
    }

    public function onGet(Request $request)
    {
        return new HtmlResponse($this->render($this->viewModel));
    }
}