<?php

namespace App\Pages;

use Obullo\Router\Router;
use Obullo\View\PageView;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestModel extends PageView
{
    public function __construct(Request $request)
    {
        $this->view = new ViewModel;
        $this->view->setTemplate('App/Pages/Test');
    }

    public function onGet(Request $request, Router $router)
    {
        return new HtmlResponse($this->render($this->view));
    }

	public function onQueryMethod(Request $request)
    {
        return new HtmlResponse('Ok');
    }
}
