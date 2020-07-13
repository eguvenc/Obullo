<?php

namespace App\Pages;

use Obullo\Router\Router;
use Obullo\View\View;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestModel extends View
{
    public function onGet(Request $request, Router $router)
    {
        $this->view = new ViewModel;
        $this->view->setTemplate('App/Pages/Test');

        return new HtmlResponse($this->render($this->view));
    }

	public function onQueryMethod(Request $request)
    {
        return new HtmlResponse('Ok');
    }
}
